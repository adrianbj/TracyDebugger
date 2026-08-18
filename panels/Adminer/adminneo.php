<?php
/**
 * AdminNeo - Powerful database manager in a single PHP file
 * v5.6.0
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
inject($xa,Config$Pb,Settings$P,Locale$kg){$this->admin=$xa;$this->config=$Pb;$this->settings=$P;$this->locale=$kg;}}abstract
class
Origin
extends
Plugin{private$errors=[];private
static$instance=null;static
function
create(array$Pb=[],array$vi=[]){if(self::$instance)die("Admin instance already exists.\n");$xa=new
static();if(!$Pb&&file_exists("adminneo-config.php")){$Pb=include_once("adminneo-config.php");if(!is_array($Pb)){$Pb=[];$cg="href=https://github.com/adminneo-org/adminneo#configuration ".target_blank();$xa->addError(lang(0,"<b>adminneo-config.php</b>")." <a $cg>".lang(1)."</a>");}}$Pb=new
Config($Pb);$P=new
Settings($Pb);if(!$vi&&file_exists("adminneo-plugins.php")){$vi=include_once("adminneo-plugins.php");if(!is_array($vi)){$vi=[];$cg="href=https://github.com/adminneo-org/adminneo#plugins ".target_blank();$xa->addError(lang(0,"<b>adminneo-plugins.php</b>")." <a $cg>".lang(1)."</a>");}}self::$instance=$vi?new
Pluginer($xa,$vi):$xa;$xa->inject(self::$instance,$Pb,$P,Locale::get());foreach($vi
as$ui)$ui->inject(self::$instance,$Pb,$P,Locale::get());return
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
verifyDefaultPassword($F){$Ce=$this->config->getDefaultPasswordHash();if($Ce===null||$Ce==="")return
lang(2);elseif(!password_verify($F,$Ce))return
lang(3);return
true;}function
authenticate($V,$F){if($F==""){$Ce=$this->config->getDefaultPasswordHash();if($Ce===null)return
lang(4,target_blank());else
return$Ce==="";}return
true;}function
getPrivateKey($Zb=false){return
get_private_key($Zb);}function
getBruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
getServerName($N,$kj=true,$Dd=null){if($N==""){if(!$kj)return"";$N=Connection::exists()?Connection::get()->getDefaultServerName():"";if($N=="")return$Dd!==null?$Dd:lang(5);$Uj=null;}else$Uj=$this->config->getServer($N);return$Uj?$Uj->getName():preg_replace('~^https?://~',"",$N);}abstract
function
getDatabase();function
getDatabases($Wd=true){return$this->filterListWithWildcards(get_databases($Wd),$this->config->getHiddenDatabases(),false,Driver::get()->getSystemDatabases());}function
getSchemas($gh=false){$Fe=$this->config->getHiddenSchemas();if($gh&&!in_array("__system",$Fe))$Fe[]="__system";return$this->filterListWithWildcards(schemas(),$Fe,false,Driver::get()->getSystemSchemas());}function
getCollations(array$_f=[]){$om=$this->config->getVisibleCollations();$Qd=$om?array_merge($om,$_f):[];return$this->filterListWithWildcards(collations(),$Qd,true);}private
function
filterListWithWildcards(array$fm,array$Qd,$Bf,array$Kk=[]){if(!$fm||!$Qd)return$fm;$s=array_search("__system",$Qd);if($s!==false){unset($Qd[$s]);$Qd=array_merge($Qd,$Kk);}array_walk($Qd,function(&$Y){$Y=str_replace('\\*',".*",preg_quote($Y,"~"));});$oi='~^('.implode("|",$Qd).')$~';return$this->filterListWithPattern($fm,$oi,$Bf);}private
function
filterListWithPattern(array$fm,$oi,$Bf){$I=[];foreach($fm
as$u=>$Y){if(is_array($Y)){if($Ak=$this->filterListWithPattern($Y,$oi,$Bf))$I[$u]=$Ak;}elseif(($Bf&&preg_match($oi,$Y))||(!$Bf&&!preg_match($oi,$Y)))$I[$u]=$Y;}return$I;}abstract
function
getQueryTimeout();function
sendHeaders(){}function
updateCspHeader(array&$dc){}function
printFavicons(){$Bb=validate_color_variant($this->config->getColorVariant());echo"<link rel='icon' type='image/x-icon' href='",link_files("favicon-$Bb.ico",[]),"' sizes='32x32'>\n","<link rel='icon' type='image/svg+xml' href='",link_files("favicon-$Bb.svg",[]),"'>\n","<link rel='apple-touch-icon' href='",link_files("apple-touch-icon-$Bb.png",[]),"'>\n";}abstract
function
printToHead();function
getCssUrls(){$Ul=$this->config->getCssUrls();foreach(["adminneo.css","adminneo-light.css","adminneo-dark.css"]as$n){if(file_exists($n))$Ul[]="$n?v=".filemtime($n);}return$Ul;}function
isLightModeForced(){return$this->isColorSchemeForced(false);}function
isDarkModeForced(){return$this->isColorSchemeForced(true);}private
function
isColorSchemeForced($ic){$Mg=$ic?Settings::$ColorSchemeDark:Settings::$ColorSchemeLight;$Ng=$ic?Settings::$ColorSchemeLight:Settings::$ColorSchemeDark;$Md=file_exists("adminneo-$Mg.css");$Nd=file_exists("adminneo-$Ng.css");if($Md&&!$Nd)return
true;return$this->settings->getColorScheme()==$Mg&&!($Md
xor$Nd);}function
getJsUrls(){$Ul=$this->config->getJsUrls();$n="adminneo.js";if(file_exists($n))$Ul[]="$n?v=".filemtime($n);return$Ul;}abstract
function
printLoginForm();function
getLoginFormRow($Hd,$Jf,$k){if($Jf)return"<tr><th>$Jf</th><td>$k</td></tr>\n";else
return"$k\n";}function
printLogout(){echo"<div class='logout'>","<form action='' method='post'>\n",h($_GET["username"]),"<input type='submit' class='button' name='logout' value='",lang(6),"' id='logout'>",input_token(),"</form>","</div>\n";}function
getTableName(array$Ok){return
h($Ok["Name"]);}abstract
function
getFieldName(array$k,$D=0);function
formatComment($Ib){return
h($Ib);}abstract
function
printTableMenu(array$Ok,$O="");function
getForeignKeys($Q){return
foreign_keys($Q);}function
getBackwardKeys($Q,$Mk){if(!$this->settings->isRelationLinks())return[];$L=backward_keys($Q);$Df=[];foreach($L
as$K){$r=$K["table_schema"].".".$K["table_name"];$Df[$r]["schema"]=$K["table_schema"];$Df[$r]["table"]=$K["table_name"];$Df[$r]["constraints"][$K["constraint_name"]][$K["column_name"]]=$K["referenced_column_name"];}foreach($Df
as$r=>$u){$A=$this->admin->getTableName(table_status1($u["table"],true));if($A!=""){$Hj=preg_quote($Mk);$Rj="(:|\\s*-)?\\s+";$Df[$r]["name"]=(preg_match("(^$Hj$Rj(.+)|^(.+?)$Rj$Hj\$)iu",$A,$y)?$y[2].$y[3]:$A);}else
unset($Df[$r]);}return$Df;}function
printBackwardKeys(array$Sa,array$K){foreach($Sa
as$u){foreach($u["constraints"]as$Sb){$zg=preg_replace('~&ns=[^&]+&~',"&ns=".urldecode($u["schema"])."&",ME);$x=$zg.'select='.urlencode($u["table"]);$q=0;foreach($Sb
as$b=>$X){if(!isset($K[$X]))continue
2;$x
.=where_link($q++,$b,$K[$X]);}$A=preg_replace('(^'.preg_quote($_GET["select"]).(substr($_GET["select"],-1)=="s"?"?":"").'_)',"_",$u["name"]);$T=implode(", ",array_keys($Sb));echo"<a href='".h($x)."' title='".h($T)."'>".h($A)."</a>";$x=$zg.'edit='.urlencode($u["table"]);foreach($Sb
as$b=>$X)$x
.="&set".urlencode("[".bracket_escape($b)."]")."=".urlencode($K[$X]);echo"<a href='".h($x)."' title='".lang(7)."'>",icon_solo("add"),"</a> ";}}}abstract
function
formatSelectQuery($H,$sk,$Cd=false);abstract
function
formatMessageQuery($H,$nl,$Cd=false);abstract
function
formatSqlCommandQuery($H);function
printAfterSqlCommand(){}abstract
function
getTableDescriptionFieldName($Q);abstract
function
fillForeignDescriptions(array$L,array$Zd);function
getFieldValueLink($X,$k){if(is_mail($X))return"mailto:$X";if(is_web_url($X))return$X;return
null;}abstract
function
formatSelectionValue($X,$x,$k,$Sh);abstract
function
formatFieldValue($Y,array$k);abstract
function
printTableStructure(array$l);abstract
function
printTablePartitions(array$ei);abstract
function
printRelatedTables(array$S);abstract
function
printTableIndexes(array$t,array$Ok);abstract
function
printSelectionColumns(array$M,array$c);abstract
function
printSelectionSearch(array$Z,array$c,array$t);abstract
function
printSelectionOrder(array$D,array$c,array$t);abstract
function
printSelectionLimit($w);abstract
function
printSelectionLength($il);abstract
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
getFieldInput($Q,array$k,$Ka,$Y,$p);function
getFieldInputHint($Q,array$k,$Y){return
support("comment")?$this->admin->formatComment($k["comment"]):"";}abstract
function
processFieldInput(array$k,$Y,$p="");function
detectJson($Id,&$Y,$Ei=null){if(is_array($Y)){$Ud=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|($this->config->isJsonValuesAutoFormat()?JSON_PRETTY_PRINT:0);$Y=json_encode($Y,$Ud);return
true;}$Ud=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|($Ei?JSON_PRETTY_PRINT:0);if(preg_match('~^jsonb?$~',$Id)){if($Y!=null&&$Ei!==null&&$this->config->isJsonValuesAutoFormat())$Y=json_encode(json_decode($Y),$Ud);return
true;}if(!$this->config->isJsonValuesDetection())return
false;if(is_string($Y)&&$Y!=""&&preg_match('~varchar|text|character varying|String|keyword~',$Id)&&($Y[0]=="{"||$Y[0]=="[")&&($yf=json_decode($Y))){if($Ei!==null&&$this->config->isJsonValuesAutoFormat())$Y=json_encode($yf,$Ud);return
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
sendDumpHeaders($Qe,$Qg=false);function
dumpDatabase($lc){}abstract
function
dumpTable($Q,$_k,$lm=0);abstract
function
dumpData($Q,$_k,$H);abstract
function
getImportFilePath();abstract
function
printDatabaseMenu();function
printNavigation($Kg){$Qf=isset($_COOKIE["neo_version"])?$_COOKIE["neo_version"]:null;echo"<div class='header'>\n",$this->admin->getServiceTitle()."\n";if($Kg!="auth"){echo"<span class='version'>",h(preg_replace('~\\.0(-|$)~','$1',VERSION));if($this->config->isVersionVerificationEnabled()&&$Qf&&version_compare(VERSION,$Qf)<0)echo"<a id='version' class='version-badge' href='https://www.adminneo.org/download' ".target_blank()." title='".h($Qf)."'>",icon_solo("asterisk"),"</a>";echo"</span>\n";if($this->config->isVersionVerificationEnabled()&&!$Qf)echo
script("verifyVersion('".js_escape(ME)."', '".get_token()."');");}echo"</div>\n";}abstract
function
printDatabaseSwitcher($Kg);function
printTablesFilter(){echo"<div class='tables-filter jsonly'>"."<input id='tables-filter' type='search' class='input' autocomplete='off' placeholder='".lang(8)."'>".script("initTablesFilter(".json_encode($this->admin->getDatabase()).");")."</div>\n";}abstract
function
printTableList(array$S);function
getSettingsRows($ve){$P=[];if($ve==1){$C=get_language_options();if($C)$P["lang"]="<tr><th id='label-language'>".lang(9)."</th>"."<td>".html_select("lang",get_language_options(),Locale::get()->getLanguage(),"","label-language")."</td></tr>\n";$C=[""=>lang(10),Settings::$ColorSchemeLight=>lang(11),Settings::$ColorSchemeDark=>lang(12)];$P["colorScheme"]="<tr><th>".lang(13)."</th>"."<td>".html_radios("colorScheme",$C,($qa=$this->settings->getParameter("colorScheme"))!==null?$qa:"")."</td></tr>\n";}elseif($ve==2){$C=[""=>lang(14),true=>lang(15),false=>lang(16),];$i=$C[$this->config->isRelationLinks()];$C[""].=" ($i)";$P["relationLinks"]="<tr><th>".lang(17)."</th>"."<td>".html_radios("relationLinks",$C,($qa=$this->settings->getParameter("relationLinks"))!==null?$qa:"")."<span class='input-hint'>".lang(18)."</span>"."</td></tr>\n";$i=$this->config->getRecordsPerPage();$C=[""=>lang(14)." ($i)","20","30","50","70","100",];$P["recordsPerPage"]="<tr><th id='label-records'>".lang(19)."</th>"."<td>".html_select("recordsPerPage",$C,($qa=$this->settings->getParameter("recordsPerPage"))!==null?$qa:"","","label-records")."<span class='input-hint'>".lang(20)."</span>"."</td></tr>\n";$i=($qa=$this->config->getEnumAsSelectThreshold())!==null?$qa:lang(21);$C=[""=>lang(14)." ($i)",-1=>lang(21),0=>lang(22),3=>lang(23,3),5=>lang(23,5),10=>lang(23,10),20=>lang(23,20),];$P["enumAsSelectThreshold"]="<tr><th id='label-enum'>".lang(24)."</th>"."<td>".html_select("enumAsSelectThreshold",$C,($qa=$this->settings->getParameter("enumAsSelectThreshold"))!==null?$qa:"","","label-enum",true)."<span class='input-hint'>".lang(25)."</span>"."</td></tr>\n";}return$P;}abstract
function
getForeignColumnInfo(array$Zd,$b);}class
Pluginer{private
static$InternalMethods=["inject"=>true,"getConfig"=>true,];private
static$AppendMethods=["getErrors"=>true,"getFieldFunctions"=>true,"getDumpOutputs"=>true,"getDumpFormats"=>true,"getSettingsRows"=>true,];private$plugins;private$hooks=[];function
__construct(Origin$xa,array$vi){$this->plugins=$vi;foreach(get_class_methods('\AdminNeo\Origin')as$Ig){$this->hooks[$Ig]=[];if(!(isset(self::$InternalMethods[$Ig])?self::$InternalMethods[$Ig]:false)){foreach($vi
as$ui){if(method_exists($ui,$Ig))$this->hooks[$Ig][]=$ui;}}if(isset(self::$AppendMethods[$Ig])?self::$AppendMethods[$Ig]:false)array_unshift($this->hooks[$Ig],$xa);else$this->hooks[$Ig][]=$xa;}}function
getPlugins(){return$this->plugins;}function
__call($A,array$Zh){$Ga=isset(self::$AppendMethods[$A])?self::$AppendMethods[$A]:false;$I=$Ga?[]:null;assert(isset($this->hooks[$A]),"Calling unknown plugin method: $A");foreach($this->hooks[$A]as$ui){$Y=call_user_func_array([$ui,$A],$Zh);if($Y!==null){if($Ga)$I+=$Y;else
return$Y;}}return$I;}function
updateCspHeader(array&$dc){$this->__call(__FUNCTION__,[&$dc]);}function
detectJson($Id,&$Y,$Ei=null){return$this->__call(__FUNCTION__,[$Id,&$Y,$Ei]);}}class
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
printLoginForm(){$Pc=Drivers::getList();$Vj=$this->config->getServerPairs($Pc);$N=SERVER?:$this->config->getDefaultServer();echo"<table class='box box-light'>\n";if($Vj)echo$this->admin->getLoginFormRow('server',lang(5),"<select name='auth[server]'>".optionlist($Vj,$N,true)."</select>");else{$Nc=DRIVER?:$this->config->getDefaultDriver($Pc);if(count($Pc)>1)echo$this->admin->getLoginFormRow('driver',lang(26),html_select("auth[driver]",$Pc,$Nc).script("initLoginDriver(qsl('select'));",""));else
echo$this->admin->getLoginFormRow('driver','',input_hidden("auth[driver]",$Nc));echo$this->admin->getLoginFormRow('server',lang(5),'<input class="input" name="auth[server]" value="'.h($N).'" title="'.lang(27).'" placeholder="localhost" autocapitalize="off">');}echo$this->admin->getLoginFormRow('username',lang(28),'<input class="input" name="auth[username]" id="username" value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'),$this->admin->getLoginFormRow('password',lang(29),'<input type="password" class="input" name="auth[password]" autocomplete="current-password">');if(!$Vj){$lc=isset($_GET["db"])?$_GET["db"]:$this->config->getDefaultDatabase();echo$this->admin->getLoginFormRow('db',lang(30),'<input class="input" name="auth[db]" value="'.h($lc).'" autocapitalize="off">');}echo"</table>\n","<p>","<input type='submit' class='button default' value='".lang(31)."'>",checkbox("auth[permanent]",1,$_COOKIE["neo_permanent"],lang(32)),"</p>\n";}function
getFieldName(array$k,$D=0){$U=$k["full_type"].($k["null"]?" NULL":"");$Ib=$k["comment"];$Rj=$U&&$Ib!=""?": ":"";return'<span title="'.h($U.$Rj.$Ib).'">'.h($k["field"]).'</span>';}function
printTableMenu(array$Ok,$O=""){echo'<p class="links top-tabs">';$dg=[];$Nj=($this->settings->isSelectionPreferred()&&!$this->settings->isNavigationReversed())||(!$this->settings->isSelectionPreferred()&&$this->settings->isNavigationReversed());if($Nj)$dg["select"]=[lang(33),"data"];if(support("table")||support("indexes"))$dg["table"]=[lang(34),"structure"];if(!$Nj)$dg["select"]=[lang(33),"data"];$Q=$Ok["Name"];$uf=false;if(support("table")){$uf=is_view($Ok);if(!$uf){if($Q!="")$dg["create"]=[lang(35),"edit"];}elseif(support("view"))$dg["view"]=[lang(36),"edit"];}if($O!==null)$dg["edit"]=[lang(7),"item-add"];foreach($dg
as$u=>$X)echo" <a href='",h(ME),"$u=",urlencode($Q),($u=="edit"?$O:""),"'",bold(isset($_GET[$u])),">",icon($X[1]),"$X[0]</a>";echo
doc_link([DIALECT=>Driver::get()->tableHelp($Q,$uf)],icon("help").lang(37)),"\n";}function
formatSelectQuery($H,$sk,$Cd=false){$Fk=support("sql");$sm=!$Cd?Driver::get()->warnings():null;if($Fk)$H
.=";";$Ik=DIALECT=="elastic"||DIALECT=="mongo"?"json":DIALECT;$J="<pre><code class='jush-$Ik'>".h(str_replace("\n"," ",$H))."</code></pre>\n";$J
.="<p class='links'>";if($Fk)$J
.="<a href='".h(ME)."sql=".urlencode($H)."'>".icon("edit").lang(38)."</a>";if($sm)$J
.="<a href='#warnings' class='toggle'>".lang(39).icon_chevron_down()."</a>";$J
.=" <span class='time'>(".format_time($sk).")</span>";$J
.="</p>\n";if($sm){$J
.=script("initToggles(qsl('p'));");$J
.="<div id='warnings' class='warnings hidden'>\n$sm\n</div>\n";}return$J;}function
formatMessageQuery($H,$nl,$Cd=false){restart_session();$He=&get_session("queries");if(!isset($He[$_GET["db"]]))$He[$_GET["db"]]=[];if(strlen($H)>1e6)$H=preg_replace('~[\x80-\xFF]+$~','',substr($H,0,1e6))."\n…";$He[$_GET["db"]][]=[$H,time(),$nl];$Fk=support("sql");$sm=!$Cd?Driver::get()->warnings():null;$pk="sql-".count($He[$_GET["db"]]);$tm="warnings-".count($He[$_GET["db"]]);$J=" ";if($sm)$J
.="<a href='#$tm' class='toggle'>".lang(39).icon_chevron_down()."</a>, ";$Qi=support("sql")?lang(40):lang(41);$J
.="<a href='#$pk' class='toggle'>$Qi".icon_chevron_down()."</a>";$J
.=" <span class='time'>".@date("H:i:s")."</span>\n";if($sm)$J
.="<div id='$tm' class='warnings hidden'>\n$sm</div>\n";$J
.="<div id='$pk' class='hidden'>\n";$Ik=DIALECT=="elastic"||DIALECT=="mongo"?"json":DIALECT;$J
.="<pre><code class='jush-$Ik'>".truncate_utf8($H,1000)."</code></pre>\n";$J
.="<p class='links'>";if($Fk)$J
.="<a href='".h(str_replace("db=".urlencode(DB),"db=".urlencode($_GET["db"]),ME).'sql=&history='.(count($He[$_GET["db"]])-1))."'>".icon("edit").lang(38)."</a>";if($nl)$J
.=" <span class='time'>($nl)</span>";$J
.="</p>\n";$J
.="</div>\n";return$J;}function
formatSqlCommandQuery($H){if(preg_match('~^DELIMITER\s~i',$H))return"";return
truncate_utf8($H,1000);}function
getTableDescriptionFieldName($Q){return"";}function
fillForeignDescriptions(array$L,array$Zd){return$L;}function
formatSelectionValue($X,$x,$k,$Sh){if($X===null)$hl="<i>NULL</i>";elseif(!$k)$hl=$X;elseif(preg_match("~char|binary|boolean~",$k["type"])&&!preg_match("~var~",$k["type"]))$hl="<code>$X</code>";elseif(is_blob($k)&&!is_utf8($X))$hl="<i>".lang(42,strlen($Sh))."</i>";elseif($this->admin->detectJson($k["full_type"],$Sh))$hl="<code class='jush-json'>$X</code>";else$hl=$X;if($x)$hl="<a href='".h($x)."'".(is_web_url($x)?target_blank():"").">$hl</a>";return$hl;}function
formatFieldValue($Y,array$k){return$Y;}function
printTableStructure(array$l){echo"<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>","<th>",lang(43),"</th>","<td>",lang(44),"</td>","<td>",lang(45),"</td>";if(support("comment"))echo"<td>",lang(46),"</td>";echo"</tr></thead>\n";$am=Driver::get()->getUserTypes();foreach($l
as$k){echo"<tr>","<th>",h($k["field"]),"</th>","<td>";$U=h($k["full_type"]);if(in_array($U,$am))echo"<a href='".h(ME.'type='.urlencode($U))."'>$U</a>";else
echo$U;if($k["null"])echo" <i>NULL</i>";if($k["auto_increment"])echo" <i>".lang(47)."</i>";$i=h($k["default"]);if(isset($k["default"]))echo" <span title='".lang(48)."'>[<b>",$k["generated"]?"<code class='jush-".DIALECT."'>$i</code>":$i,"</b>]</span>";echo"</td>","<td>",h($k["collation"]),"</td>";if(support("comment"))echo"<td>",$this->admin->formatComment($k["comment"]),"</td>";echo"\n";}echo"</table>\n","</div>\n";}function
printTablePartitions(array$ei){$ek=isset($ei["partition_names"]);echo"<p>","<code class='jush-".DIALECT."'>BY {$ei["partition_by"]} ({$ei["partition"]})</code>";if(!$ek&&isset($ei["partitions"]))echo" ".lang(49).": ".h($ei["partitions"]);echo"</p>";if($ek){echo"<table>\n","<thead><tr><th>".lang(50)."</th><td>".lang(51)."</td></tr></thead>\n";foreach($ei["partition_names"]as$u=>$A){echo"<tr><th>";if(DIALECT=="pgsql")echo"<a href='",h(ME."table=".urlencode($A)),"'>";echo
h($A);if(DIALECT=="pgsql")echo"</a>";echo"</th><td>".h($ei["partition_values"][$u])."\n";}echo"</table>\n";}}function
printRelatedTables(array$S){echo"<ul class='links'>\n";foreach($S
as$K){$x=preg_replace('~ns=[^&]*~',"ns=".urlencode($K["ns"]),ME);echo"<li><a href='",h($x."table=".urlencode($K["table"])),"'>",icon("structure");if($K["ns"]!=$_GET["ns"])echo"<b>".h($K["ns"])."</b>.";echo
h($K["table"]),"</a>";}echo"</ul>\n";}function
printTableIndexes(array$t,array$Ok){$qc=first(Driver::get()->getIndexAlgorithms($Ok));$ci=false;foreach($t
as$s){if(isset($s["partial"])?$s["partial"]:false){$ci=true;break;}}echo"<table>\n","<thead><tr>","<th>",lang(44),"</th>","<td>",lang(52)," (",lang(53),")</td>";if($ci)echo"<td>",lang(54),"</td>";echo"</tr></thead>\n";foreach($t
as$A=>$s){ksort($s["columns"]);$Gi=[];foreach($s["columns"]as$u=>$X)$Gi[]="<i>".h($X)."</i>".($s["lengths"][$u]?"(".$s["lengths"][$u].")":"").($s["descs"][$u]?" DESC":"");echo"<tr title='",h($A),"'>","<th>",$s["type"];if(isset($s['algorithm'])&&$s['algorithm']!=$qc)echo" ({$s["algorithm"]})";echo"</th>","<td>",implode(", ",$Gi),"</td>";if($ci){echo"<td>";if($s['partial'])echo"<code class='jush-",DIALECT,"'>WHERE ",h($s['partial']),"</code>";echo"</td>";}echo"</tr>\n";}echo"</table>\n";}function
printSelectionColumns(array$M,array$c){print_fieldset_start("select",lang(55),"columns",(bool)$M,true);$M[""]=[];$q=0;foreach($M
as$u=>$X){$X=isset($_GET["columns"][$u])?$_GET["columns"][$u]:[];$b=select_input("name='columns[$q][col]'",$c,isset($X["col"])?$X["col"]:null,$u!==""?"selectFieldChange":"selectAddRow");echo"<div ",($u!=""?"":"class='no-sort'"),">",icon("handle","handle jsonly");if(Driver::get()->getFunctions()||Driver::get()->getGrouping())echo
html_select("columns[$q][fun]",[-1=>""]+array_filter([lang(56)=>Driver::get()->getFunctions(),lang(57)=>Driver::get()->getGrouping()]),isset($X["fun"])?$X["fun"]:null),help_script_command("value && value.replace(/ |\$/, '(') + ')'",true),script("qsl('select').onchange = (event) => { ".($u!==""?"":" qsl('select, input:not(.remove)', event.target.parentNode).onchange();")." };",""),"($b)";else
echo$b;echo" <button class='button light remove jsonly' title='",h(lang(58)),"'>",icon_solo("remove"),"</button>",script("qsl('#fieldset-select .remove').onclick = selectRemoveRow;",""),"</div>\n";$q++;}print_fieldset_end("select",true);}function
printSelectionSearch(array$Z,array$c,array$t){print_fieldset_start("search",lang(59),"search",(bool)$Z);foreach($t
as$q=>$s){if($s["type"]=="FULLTEXT"){echo"<div>(<i>".implode("</i>, <i>",array_map('AdminNeo\h',$s["columns"]))."</i>) AGAINST","<input type='text' class='input' name='fulltext[$q]' value='".h(isset($_GET["fulltext"][$q])?$_GET["fulltext"][$q]:null)."'>",script("qsl('input').oninput = selectFieldChange;","");if(DIALECT=='sql')echo
checkbox("boolean[$q]",1,isset($_GET["boolean"][$q]),"BOOL");echo"</div>\n";}}$kb="this.parentNode.firstChild.onchange();";foreach(array_merge((array)$_GET["where"],[[]])as$q=>$X){if(!$X||("$X[col]$X[val]"!=""&&in_array($X["op"],$this->getOperators())))echo"<div>",select_input(" name='where[$q][col]'",$c,$X["col"],($X?"selectFieldChange":"selectAddRow"),"(".lang(60).")"),html_select("where[$q][op]",$this->getOperators(),$X["op"],$kb),"<input type='text' class='input' name='where[$q][val]' value='".h($X["val"])."'>",script("mixin(qsl('input'), {oninput: function () { $kb }, onkeydown: selectSearchKeydown});","")," <button class='button light remove jsonly' title='".h(lang(58))."'>",icon_solo("remove"),"</button>",script('qsl("#fieldset-search .remove").onclick = selectRemoveRow;',""),"</div>\n";}print_fieldset_end("search");}function
printSelectionOrder(array$D,array$c,array$t){print_fieldset_start("sort",lang(61),"sort",(bool)$D,true);$_GET["order"][""]="";$q=0;foreach((array)$_GET["order"]as$u=>$X){if($u!=""&&$X=="")continue;echo"<div ",($u!=""?"":"class='no-sort'"),">",icon("handle","handle jsonly"),select_input("name='order[$q]'",$c,$X,$u!==""?"selectFieldChange":"selectAddRow")," ",checkbox("desc[$q]",1,isset($_GET["desc"][$u]),lang(62))," <button class='button light remove jsonly' title='",h(lang(58)),"'>",icon_solo("remove"),"</button>",script('qsl("#fieldset-sort .remove").onclick = selectRemoveRow;',""),"</div>\n";$q++;}print_fieldset_end("sort",true);}function
printSelectionLimit($w){echo"<fieldset><legend>".lang(63)."</legend><div class='fieldset-content'>","<input type='number' name='limit' class='input size' value='$w'>",script("qsl('input').oninput = selectFieldChange;",""),"</div></fieldset>\n";}function
printSelectionLength($il){if($il!==null)echo"<fieldset><legend>".lang(64)."</legend><div class='fieldset-content'>","<input type='number' name='text_length' class='input size' value='".h($il)."'>","</div></fieldset>\n";}function
printSelectionAction(array$t){echo"<fieldset><legend>".lang(65)."</legend><div class='fieldset-content'>","<input type='submit' class='button' value='".lang(55)."'>"," <span id='noindex' title='".lang(66)."'></span>","<script".nonce().">\n";$c=new
stdClass();foreach($t
as$s){$fc=reset($s["columns"]);if($s["type"]!="FULLTEXT"&&$fc)$c->$fc=null;}echo"const indexColumns = ".json_encode($c,JSON_UNESCAPED_UNICODE).";\n","selectFieldChange.call(gid('form')['select']);\n","</script>\n","</div></fieldset>\n";}function
processSelectionColumns(array$c,array$t){$M=[];$te=[];foreach((array)$_GET["columns"]as$u=>$X){if($X["fun"]=="count"||($X["col"]!=""&&(!$X["fun"]||in_array($X["fun"],Driver::get()->getFunctions())||in_array($X["fun"],Driver::get()->getGrouping())))){$M[$u]=apply_sql_function($X["fun"],($X["col"]!=""?idf_escape($X["col"]):"*"));if(!in_array($X["fun"],Driver::get()->getGrouping()))$te[]=$M[$u];}}return[$M,$te];}function
processSelectionSearch(array$l,array$t){$J=[];foreach($t
as$q=>$s){if($s["type"]=="FULLTEXT"&&isset($_GET["fulltext"])&&$_GET["fulltext"][$q]!="")$J[]="MATCH (".implode(", ",array_map('AdminNeo\idf_escape',$s["columns"])).") AGAINST (".q($_GET["fulltext"][$q]).(isset($_GET["boolean"][$q])?" IN BOOLEAN MODE":"").")";}foreach((array)$_GET["where"]as$Z){$yb=$Z["col"];$_h=$Z["op"];$X=$Z["val"];if("$yb$X"!=""&&in_array($_h,$this->getOperators())){$Ob=[];foreach(($yb!=""?[$yb=>$l[$yb]]:$l)as$A=>$k){$Ci="";$Nb=" $_h";$ph=DIALECT=="pgsql"&&$_h=="="&&$k["type"]=="oid";if($ph)$Nb
.=" ".$this->admin->processFieldInput($k,$X)."::regproc";elseif(preg_match('~IN$~',$_h)){$Ve=process_length($X);$Nb
.=" ".($Ve!=""?$Ve:"(NULL)");}elseif($_h=="SQL")$Nb=" $X";elseif(preg_match('~^(I?LIKE) %%$~',$_h,$y))$Nb=" $y[1] ".$this->admin->processFieldInput($k,"%$X%");elseif($_h=="FIND_IN_SET"){$Ci="$_h(".q($X).", ";$Nb=")";}elseif(!preg_match('~NULL$~',$_h))$Nb
.=" ".$this->admin->processFieldInput($k,$X);if($yb!=""||(isset($k["privileges"]["where"])&&(preg_match('~^[-\d.'.(preg_match('~IN$~',$_h)?',':'').']+$~',$X)||!preg_match('~'.number_type().'|bit~',$k["type"]))&&(!preg_match("~[\x80-\xFF]~",$X)||preg_match('~char|text|enum|set~',$k["type"]))&&(!preg_match('~date|timestamp~',$k["type"])||preg_match('~^\d+-\d+-\d+~',$X))&&(!preg_match('~^elastic~',DRIVER)||$k["type"]!="boolean"||preg_match('~true|false~',$X))&&(!preg_match('~^elastic~',DRIVER)||strpos($_h,"regexp")===false||preg_match('~text|keyword~',$k["type"])))){if($ph)$Ob[]=$Ci.idf_escape($A).$Nb;else$Ob[]=$Ci.Driver::get()->convertSearch(idf_escape($A),$Z,$k).$Nb;}}if(count($Ob)==1)$J[]=$Ob[0];elseif($Ob)$J[]="(".implode(" OR ",$Ob).")";else$J[]="1 = 0";}}return$J;}function
processSelectionOrder(array$l,array$t){$J=[];foreach((array)$_GET["order"]as$u=>$X){if($X!="")$J[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~',$X)?$X:idf_escape($X)).(isset($_GET["desc"][$u])?" DESC":"");}return$J;}function
processSelectionLength(){return
isset($_GET["text_length"])?$_GET["text_length"]:"100";}function
getFieldFunctions(array$k){$J=($k["null"]?"NULL/":"");$Rl=isset($_GET["select"])||where($_GET);foreach([Driver::get()->getInsertFunctions(),Driver::get()->getEditFunctions()]as$u=>$le){if(!$u||(!isset($_GET["call"])&&$Rl)){foreach($le
as$oi=>$X){if(!$oi||preg_match("~$oi~",$k["type"]))$J
.="/$X";}}if($u&&$le&&!preg_match('~enum|set|bool~',$k["type"])&&!is_blob($k))$J
.="/SQL";}if($k["auto_increment"]&&!$Rl)$J=lang(47);return
explode("/",$J);}function
getFieldInput($Q,array$k,$Ka,$Y,$p){return"";}function
processFieldInput(array$k,$Y,$p=""){if($p=="SQL")return$Y;if(isset($k["full_type"]))$this->admin->detectJson($k["full_type"],$Y,false);$A=$k["field"];$J=q($Y);if(preg_match('~^(now|getdate|uuid)$~',$p))$J="$p()";elseif(preg_match('~^current_(date|timestamp)$~',$p))$J=$p;elseif(preg_match('~^([+-]|\|\|)$~',$p))$J=idf_escape($A)." $p $J";elseif(preg_match('~^[+-] interval$~',$p))$J=idf_escape($A)." $p ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$Y)&&DIALECT!="pgsql"?$Y:$J);elseif(preg_match('~^(addtime|subtime|concat)$~',$p))$J="$p(".idf_escape($A).", $J)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$p))$J="$p($J)";elseif($k["type"]=="boolean"&&DIALECT=="elastic")$J=$J=="0"?"false":"true";return
unconvert_field($k,$J);}function
getDumpOutputs(){$Vh=['file'=>lang(67),'text'=>lang(68),];if(function_exists('gzencode'))$Vh['gz']='gzip';return$Vh;}function
getDumpFormats(){return(support("dump")?['sql'=>'SQL']:[])+['csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV'];}function
sendDumpHeaders($Qe,$Qg=false){$Uh=$_POST["output"];$zd=(str_contains($_POST["format"],"sql")?"sql":($Qg?"tar":"csv"));if($Uh=="gz"){header("Content-Type: application/x-gzip");ob_start(function($xk){return
gzencode($xk);},1e6);}elseif($zd=="tar")header("Content-Type: application/x-tar");elseif($zd=="sql"||$Uh=="text")header("Content-Type: text/plain; charset=utf-8");else
header("Content-Type: text/csv; charset=utf-8");return$zd;}function
dumpTable($Q,$_k,$lm=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($_k)dump_csv(array_keys(fields($Q)));}else{if($lm==2){$l=[];foreach(fields($Q)as$A=>$k)$l[]=idf_escape($A)." $k[full_type]";$Zb="CREATE TABLE ".table($Q)." (".implode(", ",$l).")";}else$Zb=create_sql($Q,$_POST["auto_increment"],$_k);set_utf8mb4($Zb);if($_k&&$Zb){if($_k=="DROP+CREATE"||$lm==1)echo"DROP ".($lm==2?"VIEW":"TABLE")." IF EXISTS ".table($Q).";\n";if($lm==1)$Zb=remove_definer($Zb);echo"$Zb;\n\n";}}}function
dumpData($Q,$_k,$H){if($_k){$sg=(DIALECT=="sqlite"?0:1048576);$l=[];$Re=false;if($_POST["format"]=="sql"){if($_k=="TRUNCATE+INSERT")echo
truncate_sql($Q).";\n";$l=fields($Q);if(DIALECT=="mssql"){foreach($l
as$k){if($k["auto_increment"]){echo"SET IDENTITY_INSERT ".table($Q)." ON;\n";$Re=true;break;}}}}$I=Connection::get()->query($H,1);if($I){$gf="";$cb="";$Df=[];$ne=[];$Ck="";$Yb=0;while($K=($Q!=''?$I->fetchAssoc():$I->fetchRow())){if(!$Df){$fm=[];foreach($K
as$X){$k=$I->fetchField();if(!empty($l[$k->name]['generated'])){$ne[$k->name]=true;continue;}$Df[]=$k->name;$u=idf_escape($k->name);$fm[]="$u = VALUES($u)";}$Ck=($_k=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$fm):"").";\n";}if($_POST["format"]!="sql"){if($_k=="table"){dump_csv($Df);$_k="INSERT";}dump_csv($K);}else{if(!$gf)$gf="INSERT INTO ".table($Q)." (".implode(", ",array_map('AdminNeo\idf_escape',$Df)).") VALUES";foreach($K
as$u=>$X){if(isset($ne[$u])){unset($K[$u]);continue;}$k=$l[$u];$K[$u]=($X===null?"NULL":($X===false?0:unconvert_field($k,preg_match(number_type(),$k["type"])&&!preg_match('~\[~',$k["full_type"])&&is_numeric($X)?$X:(!is_blob($k)||is_utf8($X)?q($X):Driver::get()->quoteBinary($X)))));}$wj=($sg?"\n":" ")."(".implode(",\t",$K).")";if(!$cb)$cb=$gf.$wj;elseif(DIALECT=="mssql"?$Yb%1000!=0:strlen($cb)+4+strlen($wj)+strlen($Ck)<$sg)$cb
.=",$wj";else{echo$cb.$Ck;$cb=$gf.$wj;}}$Yb++;}if($cb)echo$cb.$Ck;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",Connection::get()->getError())."\n";if($Re)echo"SET IDENTITY_INSERT ".table($Q)." OFF;\n";}}function
getImportFilePath(){return"adminneo.sql";}function
printDatabaseMenu(){echo"<p class='links top-links'>\n";$ih=isset($_GET["ns"])?$_GET["ns"]:null;if($ih==""&&support("database"))echo'<a href="',h(ME),'database=">',icon("edit"),lang(69),"</a>\n";if($ih!=""&&support("scheme"))echo"<a href='",h(ME),"scheme='>",icon("edit"),lang(70),"</a>\n";if($ih!=="")echo'<a href="',h(ME),'schema=">',icon("schema"),lang(71),"</a>\n";if(support("privileges"))echo"<a href='",h(ME),"privileges='>",icon("users"),lang(72),"</a>\n";echo"</p>\n";}function
printNavigation($Kg){parent::printNavigation($Kg);if($Kg=="auth"){$Uh="";foreach((array)$_SESSION["pwds"]as$hm=>$Zj){foreach($Zj
as$N=>$bm){foreach($bm
as$V=>$F){if($F!==null){$oc=$_SESSION["db"][$hm][$N][$V];foreach(($oc?array_keys($oc):[""])as$h){$Wj=$this->admin->getServerName($N,false);$T=h(get_driver_name($hm,$N)).($V!=""||$Wj!=""?" - ":"").h($V).($V!=""&&$Wj!=""?"@":"").h($Wj).($h!=""?h(" - $h"):"");$Uh
.="<li><a href='".h(auth_url($hm,$N,$V,$h))."' class='primary' title='$T'>$T</a></li>\n";}}}}}if($Uh)echo"<nav id='logins'><menu>\n$Uh</menu></nav>\n";}else{$this->admin->printDatabaseSwitcher($Kg);$ua=[];if(DB==""||!$Kg){if(support("sql")){$ua[]="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".icon("command").lang(40)."</a>";$ua[]="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".icon("import").lang(73)."</a>";}$ua[]="<a href='".h(ME)."dump=".urlencode(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".icon("export").lang(74)."</a>";}if(DB=="")$ua[]='<a href="'.h(ME).'database="'.bold($_GET["database"]==="").">".icon("database-add").lang(75)."</a>\n";if(DB!=""&&$_GET["ns"]===""&&!$Kg)$ua[]='<a href="'.h(ME).'scheme="'.bold($_GET["scheme"]==="").">".icon("database-add").lang(76)."</a>\n";if(DB!=""&&$_GET["ns"]!==""&&!$Kg)$ua[]='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".icon("table-add").lang(77)."</a>\n";if($ua)echo"<p class='links'>".implode("\n",$ua)."</p>";$S=[];if($_GET["ns"]!==""&&!$Kg&&DB!=""){Connection::get()->selectDatabase(DB);$S=table_status('',true);}if($_GET["ns"]!==""&&!$Kg&&DB!=""){if($S){$this->admin->printTablesFilter();$this->admin->printTableList($S);}else
echo"<p class='message'>".lang(78)."</p>\n";}if(support("sql")||DIALECT=="elastic"||DIALECT=="mongo"){echo"<script".nonce().">\n";if(support("sql")&&$S){$dg=[];foreach($S
as$Q=>$U)$dg[]=preg_quote($Q,'/');$Nk=support("table")&&!$this->config->isSelectionPreferred()?"table":"select";echo"window.jushLinks = { ".DIALECT.": {\n",js_escape_key(ME.$Nk.'=$&'),': /\b('.implode('|',$dg).')\b/g';if(support('routine')){foreach(routines()as$K)echo",\n",js_escape_key(ME.'function='.urlencode($K["SPECIFIC_NAME"]).'&name=$&'),': /\b'.preg_quote($K["ROUTINE_NAME"],'/').'(?=["`]?\()/g';}echo"\n}};\n";foreach(["bac","bra","sqlite_quo","mssql_bra"]as$X)echo"jushLinks.$X = jushLinks.".DIALECT.";\n";}if(DIALECT!="elastic"&&DIALECT!="mongo"&&$this->getConfig()->isSqlAutocompletionEnabled()&&(isset($_GET["sql"])||isset($_GET["trigger"])||isset($_GET["check"]))){$Xk=array_fill_keys(array_keys($S),[]);foreach(Driver::get()->getAllFields()as$Q=>$l){foreach($l
as$k)$Xk[$Q][]=$k["field"];}echo"window.addEventListener('DOMContentLoaded', () => { autocompletion = jush.autocompleteSql('".idf_escape("")."', ".json_encode($Xk)."); });\n";}echo"</script>\n";}echo
script("let autocompletion;\nwindow.addEventListener('DOMContentLoaded', () => { initSyntaxHighlighting('".js_escape(doc_version())."', '".js_escape(Connection::get()->getFlavor())."', autocompletion); });");}}function
printDatabaseSwitcher($Kg){$g=$this->admin->getDatabases();if(!$g&&DIALECT!="sqlite")return;echo"<div class='db-selector'><form action=''>";hidden_fields_get();echo"<div>";if($g)echo"<select id='database-select' name='db' title='",lang(30),"'>".optionlist([""=>"(".lang(79).")"]+$g,DB)."</select>".script("mixin(gid('database-select'), {onmousedown: dbMouseDown, onchange: dbChange});");else
echo"<input id='database-select' class='input' name='db' value='".h(DB)."' title='",lang(30),"' autocapitalize='off'>\n";echo"<input type='submit' value='".lang(80)."' class='button ".($g?"hidden":"")."'>\n","</div>";foreach(["import","sql","schema","dump","privileges"]as$X){if(isset($_GET[$X])){echo
input_hidden($X);break;}}echo"</form></div>\n";}function
printTableList(array$S){$Ag=($this->settings->isNavigationDual()?"class='dual'":($this->settings->isNavigationReversed()?"class='reversed'":""));echo"<nav id='tables'><menu $Ag>";foreach($S
as$Q=>$uk){$Q="$Q";$A=$this->admin->getTableName($uk);if($A==""||(isset($uk["Partition"])?$uk["Partition"]:false))continue;echo"<li>";$va=in_array($Q,[$_GET["table"],$_GET["select"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"]]);$wb="primary".(is_view($uk)?" view":"");$Gk=support("table")||support("indexes");$Kj=h(ME)."select=".urlencode($Q);$Pk=h(ME)."table=".urlencode($Q);if($this->settings->isSelectionPreferred()){if($this->settings->isNavigationReversed()&&$Gk)echo" <a href='$Pk' title='",lang(34),"' class='secondary'>",icon("structure"),"</a>";echo"<a href='$Kj'",bold($va,$wb)," data-primary='true' title='$A'>$A</a>";if($this->settings->isNavigationDual()&&$Gk)echo" <a href='$Pk' title='",lang(34),"' class='secondary'>",icon_solo("structure"),"</a>";}else{if($this->settings->isNavigationReversed())echo" <a href='$Kj' title='",lang(33),"' class='secondary'>",icon("data"),"</a>";if($Gk)echo"<a href='$Pk'",bold($va,$wb)," data-primary='true' title='$A'>$A</a>";else
echo"<span data-primary='true'",bold($va,$wb),">$A</span>";if($this->settings->isNavigationDual())echo" <a href='$Kj' title='",lang(33),"' class='secondary'>",icon_solo("data"),"</a>";}echo"</li>\n";}echo"</menu></nav>\n",script("initTablesList(".json_encode($this->admin->getDatabase()).");");}function
getSettingsRows($ve){$P=parent::getSettingsRows($ve);if($ve==1){$C=[""=>lang(14),Config::$NavigationSimple=>lang(81),Config::$NavigationDual=>lang(82),Config::$NavigationReversed=>lang(83)];$i=$C[$this->config->getNavigationMode()];$C[""].=" ($i)";$P["navigationMode"]="<tr><th>".lang(84)."</th>"."<td>".html_radios("navigationMode",$C,($qa=$this->settings->getParameter("navigationMode"))!==null?$qa:"")."<span class='input-hint'>".lang(85)."</span>"."</td></tr>\n";$C=[""=>lang(14),0=>lang(34),1=>lang(33),];$i=$C[$this->config->isSelectionPreferred()?1:0];$C[""].=" ($i)";$P["preferSelection"]="<tr><th id='label-links'>".lang(86)."</th>"."<td>".html_select("preferSelection",$C,($qa=$this->settings->getParameter("preferSelection"))!==null?$qa:"","","label-links",true)."<span class='input-hint'>".lang(87)."</span>"."</td></tr>\n";}return$P;}function
getForeignColumnInfo(array$Zd,$b){return
null;}}class
TmpFile{private$handler;private$size;function
__construct(){$this->handler=tmpfile();}function
getSize(){return$this->size;}function
write($Ub){if(!$this->handler)return;$this->size+=strlen($Ub);fwrite($this->handler,$Ub);}function
send(){if(!$this->handler)return;fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}function
print_select_result(Result$I,$e=null,array$Mh=[],$w=0){$dg=[];$t=[];$c=[];$Ya=[];$Hl=[];$J=[];for($q=0;(!$w||$q<$w)&&($K=$I->fetchRow());$q++){if(!$q){echo"<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>";for($xf=0;$xf<count($K);$xf++){$k=$I->fetchField();if(!$k){echo"<th></th>";continue;}$A=$k->name;$Lh=isset($k->orgtable)?$k->orgtable:"";$Kh=isset($k->orgname)?$k->orgname:$A;if(isset($k->table))$J[$k->table]=$Lh;if($Mh&&DIALECT=="sql")$dg[$xf]=($A=="table"?"table=":($A=="possible_keys"?"indexes=":null));elseif($Lh!=""){if(!isset($t[$Lh])){$t[$Lh]=[];foreach(indexes($Lh,$e)as$s){if($s["type"]=="PRIMARY"){$t[$Lh]=array_flip($s["columns"]);break;}}$c[$Lh]=$t[$Lh];}if(isset($c[$Lh][$Kh])){unset($c[$Lh][$Kh]);$t[$Lh][$Kh]=$xf;$dg[$xf]=$Lh;}}if($k->charsetnr==63)$Ya[$xf]=true;$Hl[$xf]=$k->type;echo"<th".($Lh!=""||$k->name!=$Kh?" title='".h(($Lh!=""?"$Lh.":"").$Kh)."'":"").">".h($A).($Mh?doc_link(['sql'=>"explain-output.html#explain_".strtolower($A),'mariadb'=>"reference/sql-statements/administrative-sql-statements/analyze-and-explain-statements/explain#columns-in-explain-...-select",]):"");}echo"</thead>\n";}echo"<tr>";foreach($K
as$u=>$X){$x="";if(isset($dg[$u])&&!$c[$dg[$u]]){if($Mh&&DIALECT=="sql"){$Q=$K[array_search("table=",$dg)];$x=ME.$dg[$u].urlencode($Mh[$Q]!=""?$Mh[$Q]:$Q);}else{$x=ME."edit=".urlencode($dg[$u]);foreach($t[$dg[$u]]as$yb=>$xf)$x
.="&where".urlencode("[".bracket_escape($yb)."]")."=".urlencode($K[$xf]);}}$U=($Ya[$u]?'blob':($Hl[$u]==254?'char':''));$k=['full_type'=>$U,'type'=>$U,];$X=select_value($X,$x,$k,null);$wb=$Hl[$u]<=9||$Hl[$u]==246?"class='number'":"";echo"<td $wb>$X</td>";}}if($q)echo"</table>\n</div>";else
echo"<p class='message'>".lang(88);echo"\n";return$J;}function
referencable_primary($Pj){$J=[];foreach(table_status('',true)as$Rk=>$Q){if($Rk!=$Pj&&fk_support($Q)){foreach(fields($Rk)as$k){if($k["primary"]){if($J[$Rk]){unset($J[$Rk]);break;}$J[$Rk]=$k;}}}}return$J;}function
textarea($A,$Y,$L=10,$Db=80){echo"<textarea name='".h($A)."' rows='$L' cols='$Db' class='sqlarea jush-".DIALECT."' spellcheck='false' wrap='off'>";if(is_array($Y)){foreach($Y
as$X)echo
h($X[0])."\n\n\n";}else
echo
h($Y);echo"</textarea>";}function
select_input($Ka,$C,$Y="",$yh="",$ri=""){$bl=($C?"select":"input");return"<$bl $Ka".($C?"><option value=''>$ri".optionlist($C,$Y,true)."</select>":" size='10' value='".h($Y)."' placeholder='$ri'>").($yh?script("qsl('$bl').onchange = $yh;",""):"");}function
json_row($u,$X=null){static$Sd=true;if($Sd)echo"{";if($u!=""){echo($Sd?"":",")."\n\t\"".addcslashes($u,"\r\n\t\"\\/").'": '.($X!==null?'"'.addcslashes($X,"\r\n\t\"\\/").'"':'null');$Sd=false;}else{echo"\n}\n";$Sd=true;}}function
edit_type($u,$k,$Ab,$ae=[],$Bd=[]){$U=isset($k["type"])?$k["type"]:null;echo'<td><select name="',h($u),'[type]" class="type" aria-labelledby="label-type">';$Oc=Driver::get()->getTypes();if($U&&!isset($Oc[$U])&&!isset($ae[$U])&&!in_array($U,$Bd))$Bd[]=$U;$zk=Driver::get()->getStructuredTypes();if($ae)$zk[lang(89)]=$ae;echo
optionlist(array_merge($Bd,$zk),$U),'</select><td><input name="',h($u),'[length]" value="',h(isset($k["length"])?$k["length"]:null),'" size="3"',(!(isset($k["length"])?$k["length"]:null)&&preg_match('~var(char|binary)$~',$U)?" class='input required'":" class='input'"),' aria-labelledby="label-length"><td class="options">',($Ab?"<select name='".h($u)."[collation]'".(preg_match('~(char|text|enum|set)$~',$U)?"":" class='hidden'").'><option value="">('.lang(90).')'.optionlist($Ab,isset($k["collation"])?$k["collation"]:null).'</select>':''),(Driver::get()->getUnsigned()?"<select name='".h($u)."[unsigned]'".(!$U||preg_match(number_type(),$U)?"":" class='hidden'").'><option>'.optionlist(Driver::get()->getUnsigned(),isset($k["unsigned"])?$k["unsigned"]:null).'</select>':''),(isset($k['on_update'])?"<select name='".h($u)."[on_update]'".(preg_match('~timestamp|datetime~',$U)?"":" class='hidden'").'>'.optionlist([""=>"(".lang(91).")","CURRENT_TIMESTAMP"],(preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?"CURRENT_TIMESTAMP":$k["on_update"])).'</select>':''),($ae?"<select name='".h($u)."[on_delete]'".(preg_match("~`~",$U)?"":" class='hidden'")."><option value=''>(".lang(92).")".optionlist(Driver::get()->getOnActions(),isset($k["on_delete"])?$k["on_delete"]:null)."</select> ":" ");}function
process_length($v){$jd=Driver::$EnumLengthPattern;return(preg_match("~^\\s*\\(?\\s*$jd(?:\\s*,\\s*$jd)*+\\s*\\)?\\s*\$~",$v)&&preg_match_all("~$jd~",$v,$z)?"(".implode(",",$z[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$v)));}function
process_type($k,$zb="COLLATE"){return" $k[type]".process_length($k["length"]).(preg_match(number_type(),$k["type"])&&in_array($k["unsigned"],Driver::get()->getUnsigned())?" $k[unsigned]":"").(preg_match('~char|text|enum|set~',$k["type"])&&$k["collation"]?" $zb ".(DIALECT=="mssql"?$k["collation"]:q($k["collation"])):"");}function
process_field($k,$Fl){if($k["on_update"])$k["on_update"]=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$k["on_update"]);return[idf_escape(trim($k["field"])),process_type($Fl),($k["null"]?" NULL":" NOT NULL"),default_value($k),(preg_match('~timestamp|datetime~',$k["type"])&&$k["on_update"]?" ON UPDATE ".$k["on_update"]:""),(support("comment")&&$k["comment"]!=""?" COMMENT ".q($k["comment"]):""),($k["auto_increment"]?auto_increment():null),];}function
default_value($k){if($k["default"]===null)return"";$i=str_replace("\r","",$k["default"]);$me=$k["generated"];if(in_array($me,Driver::get()->getGenerated())){if(DIALECT=="mssql")return" AS ($i)".($me=="VIRTUAL"?"":" $me");else
return" GENERATED ALWAYS AS ($i) $me";}if(stripos($i,"GENERATED ")===0)return" $i";if(preg_match('~char|binary|text|json|enum|set~',$k["type"])||preg_match('~^(?![a-z])~i',$i)){if(DIALECT=="sql"&&preg_match('~text|json~',$k["type"]))return" DEFAULT (".q($i).")";else
return" DEFAULT ".q($i);}else{$i=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$i);return" DEFAULT ".(DIALECT=="sqlite"?"($i)":$i);}}function
type_class($U){foreach(['char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',]as$wb=>$oi){if(preg_match("~$wb|$oi~",$U))return"class='$wb'";}return"";}function
edit_fields(array$l,array$Ab,$U="TABLE",$ae=[]){$l=array_values($l);$Lb=$_POST?$_POST["comments"]:Admin::get()->getSettings()->getParameter("commentsOpened");$Jb=$Lb?"":"class='hidden'";echo"<thead><tr>\n";if(support("move_col"))echo"<td class='jsonly'></td>";if($U=="PROCEDURE")echo"<td></td>";echo"<th id='label-name'>",($U=="TABLE"?lang(93):lang(94)),"</th>\n","<td id='label-type'>",lang(44),"<textarea id='enum-edit' rows='4' cols='12' wrap='off' style='display: none;'></textarea>",script("gid('enum-edit').onblur = onFieldLengthBlur;"),"</td>\n","<td id='label-length'>",lang(95),"</td>\n","<td>",lang(96),"</td>\n";if($U=="TABLE")echo"<td id='label-null'>NULL</td>\n","<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='",lang(47),"'>AI</abbr>",doc_link(['sql'=>"example-auto-increment.html",'mariadb'=>"reference/data-types/auto_increment",]),"</td>\n","<td id='label-default'>",lang(48),"</td>\n",support("comment")?"<td id='label-comment' $Jb>".lang(46)."</td>\n":"";echo"<td>","<button name='add[",(support("move_col")?0:count($l)),"]' value='1' title='",h(lang(97)),"' class='button light'>",icon_solo("add"),"</button>",script("row_count = ".count($l).";"),"</td>\n","</tr></thead>\n";$wb=support("move_col")?"class='sortable'":"";echo"<tbody $wb>\n";foreach($l
as$q=>$k){$q++;$Nh=$k[($_POST?"orig":"field")];$Ec=(isset($_POST["add"][$q-1])||(isset($k["field"])&&!(isset($_POST["drop_col"][$q])?$_POST["drop_col"][$q]:null)))&&(support("drop_col")||$Nh=="");$_k=$Ec?"":"style='display: none;'";echo"<tr $_k>\n";if(support("move_col"))echo"<td class='handle jsonly'>",icon_solo("handle"),"</td>";if($U=="PROCEDURE")echo"<td>",html_select("fields[$q][inout]",Driver::get()->getInOut(),$k["inout"]),"</td>\n";echo"<th>";if($Ec)echo"<input class='input' name='fields[$q][field]' value='",h($k["field"]),"' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name' ".(isset($_POST["add"][$q-1])?"autofocus":"").">";echo
input_hidden("fields[$q][orig]",$Nh);edit_type("fields[$q]",$k,$Ab,$ae);echo"</th>\n";if($U=="TABLE"){echo"<td>",checkbox("fields[$q][null]",1,$k["null"],"","","block","label-null"),"</td>\n";$rb=$k["auto_increment"]?"checked":"";echo"<td><label class='block'><input type='radio' name='auto_increment_col' value='$q' $rb aria-labelledby='label-ai'></label></td>\n","<td class='default-value'>";if(Driver::get()->getGenerated())echo
html_select("fields[$q][generated]",array_merge(["","DEFAULT"],Driver::get()->getGenerated()),$k["generated"]);else
echo
checkbox("fields[$q][generated]",1,$k["generated"],"","","","label-default");$Ka="name='fields[$q][default]' aria-labelledby='label-default'";$Y=h($k["default"]);if(str_contains($Y,"\n")){if($Y[0]=="\n")$Y="\n$Y";echo"<textarea $Ka rows='3' cols='30' style='vertical-align: bottom;'>$Y</textarea>";}else
echo"<input class='input' $Ka value='$Y'>";echo"</td>\n";if(support("comment")){$rg=Connection::get()->isMinVersion("5.5")?1024:255;echo"<td $Jb>","<input class='input' name='fields[$q][comment]' value='",h($k["comment"]),"' data-maxlength='$rg' aria-labelledby='label-comment'>","</td>\n";}}echo"<td>";if(support("move_col"))echo"<button name='add[$q]' value='1' title='".h(lang(97))."' class='button light'>",icon_solo("add"),"</button>","<button name='up[$q]' value='1' title='".h(lang(98))."' class='button light hidden'>",icon_solo("arrow-up"),"</button>","<button name='down[$q]' value='1' title='".h(lang(99))."' class='button light hidden'>",icon_solo("arrow-down"),"</button>";if($Nh==""||support("drop_col"))echo"<button name='drop_col[$q]' value='1' title='".h(lang(58))."' class='button light'>",icon_solo("remove"),"</button>";echo"</td>\n</tr>\n";}echo"</tbody>";}function
process_fields(&$l){$nh=0;if($_POST["up"]){$Of=0;foreach($l
as$u=>$k){if(key($_POST["up"])==$u){unset($l[$u]);array_splice($l,$Of,0,[$k]);break;}if(isset($k["field"]))$Of=$nh;$nh++;}}elseif($_POST["down"]){$fe=false;foreach($l
as$u=>$k){if(isset($k["field"])&&$fe){unset($l[key($_POST["down"])]);array_splice($l,$nh,0,[$fe]);break;}if(key($_POST["down"])==$u)$fe=$k;$nh++;}}elseif($_POST["add"]){$l=array_values($l);array_splice($l,key($_POST["add"]),0,[[]]);}elseif(!$_POST["drop_col"])return
false;return
true;}function
normalize_enum($y){$X=$y[0];return"'".str_replace("'","''",addcslashes(stripcslashes(str_replace($X[0].$X[0],$X[0],substr($X,1,-1))),'\\'))."'";}function
grant($qe,array$Ji,$c,$wh,$Zl){if(!$Ji)return
true;if($Ji==["ALL PRIVILEGES","GRANT OPTION"]){if($qe)return(bool)queries("GRANT ALL PRIVILEGES ON $wh TO $Zl WITH GRANT OPTION");else
return
queries("REVOKE ALL PRIVILEGES ON $wh FROM $Zl")&&queries("REVOKE GRANT OPTION ON $wh FROM $Zl");}if($Ji==["GRANT OPTION","PROXY"]){if($qe)return(bool)queries("GRANT PROXY ON $wh TO $Zl WITH GRANT OPTION");else
return(bool)queries("REVOKE PROXY ON $wh FROM $Zl");}return(bool)queries(($qe?"GRANT ":"REVOKE ").preg_replace('~(GRANT OPTION)\([^)]*\)~','$1',implode("$c, ",$Ji).$c)." ON $wh ".($qe?"TO ":"FROM ").$Zl);}function
drop_create($Qc,$Zb,$Rc,$gl,$Sc,$lg,$Dg,$Bg,$Cg,$uh,$dh){if($_POST["drop"])query_redirect($Qc,$lg,$Dg);elseif($uh=="")query_redirect($Zb,$lg,$Cg);elseif($uh!=$dh){$cc=queries($Zb);queries_redirect($lg,$Bg,$cc&&queries($Qc));if($cc)queries($Rc);}else
queries_redirect($lg,$Bg,queries($gl)&&queries($Sc)&&queries($Qc)&&queries($Zb));}function
create_trigger($wh,array$Al){$pl=" $Al[Timing] $Al[Event]".(preg_match('~ OF~',$Al["Event"])?" $Al[Of]":"");return"CREATE TRIGGER ".idf_escape($Al["Trigger"]).(DIALECT=="mssql"?$wh.$pl:$pl.$wh).rtrim(" $Al[Type]\n$Al[Statement]",";").";";}function
create_routine($sj,$K){$O=[];$l=(array)$K["fields"];ksort($l);$We=implode("|",Driver::get()->getInOut());foreach($l
as$k){if($k["field"]!="")$O[]=(preg_match("~^($We)\$~",$k["inout"])?"$k[inout] ":"").idf_escape($k["field"]).process_type($k,"CHARACTER SET");}$uc=rtrim($K["definition"],";");return"CREATE $sj ".idf_escape(trim($K["name"]))." (".implode(", ",$O).")".($sj=="FUNCTION"?" RETURNS".process_type($K["returns"],"CHARACTER SET"):"").($K["language"]?" LANGUAGE $K[language]":"").(DIALECT=="pgsql"?" AS ".q($uc):"\n$uc;");}function
remove_definer($H){return
preg_replace('~^([A-Z =]+) DEFINER=`'.preg_replace('~@(.*)~','`@`(%|\1)',logged_user()).'`~','\1',$H);}function
format_foreign_key($o){$xh=implode("|",Driver::get()->getOnActions());$h=$o["db"];$ih=$o["ns"];return" FOREIGN KEY (".implode(", ",array_map('AdminNeo\idf_escape',$o["source"])).") REFERENCES ".($h!=""&&$h!=$_GET["db"]?idf_escape($h).".":"").($ih!=""&&$ih!=$_GET["ns"]?idf_escape($ih).".":"").idf_escape($o["table"])." (".implode(", ",array_map('AdminNeo\idf_escape',$o["target"])).")".(preg_match("~^($xh)\$~",$o["on_delete"])?" ON DELETE $o[on_delete]":"").(preg_match("~^($xh)\$~",$o["on_update"])?" ON UPDATE $o[on_update]":"").(isset($o["deferrable"])?" $o[deferrable]":"");}function
tar_file($n,TmpFile$sl){$Ee=pack("a100a8a8a8a12a12",$n,644,0,0,decoct($sl->getSize()),decoct(time()));$tb=8*32;for($q=0;$q<strlen($Ee);$q++)$tb+=ord($Ee[$q]);$Ee
.=sprintf("%06o",$tb)."\0 ";echo$Ee,str_repeat("\0",512-strlen($Ee));$sl->send();echo
str_repeat("\0",511-($sl->getSize()+511)%512);}function
doc_link(array$ni,$hl="<sup>?</sup>"){if(!(isset($ni[DIALECT])?$ni[DIALECT]:null))return"";$im=doc_version();$Ul=['sql'=>"https://dev.mysql.com/doc/refman/$im/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(Connection::get()->isCockroachDB()?"current":$im)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://www.oracle.com/pls/topic/lookup?ctx=db".str_replace(".","",$im)."&id=",'elastic'=>"https://www.elastic.co/guide/en/elasticsearch/reference/$im/",];if(Connection::get()->isMariaDB()){$Ul['sql']="https://mariadb.com/docs/server/";$ni['sql']=isset($ni['mariadb'])?$ni['mariadb']:str_replace(".html","",$ni['sql']);}return"<a href='".h($Ul[DIALECT].$ni[DIALECT].(DIALECT=='mssql'?"?view=sql-server-ver$im":""))."'".target_blank().">$hl</a>";}function
doc_version(){return
preg_replace('~^(\d\.?\d).*~s','\1',Connection::get()->getVersion());}function
db_size($h){if(!Connection::get()->selectDatabase($h))return"?";$J=0;foreach(table_status()as$R)$J+=$R["Data_length"]+$R["Index_length"];return
format_number($J);}function
set_utf8mb4($Zb){static$O=false;if(!$O&&preg_match('~\butf8mb4~i',$Zb)){$O=true;echo"SET NAMES ".charset(Connection::get()).";\n\n";}}error_reporting(E_ALL&~E_DEPRECATED);set_error_handler(function($ld,$j){return(bool)preg_match('~^Undefined (array key|offset|index)~',$j);},E_WARNING|E_NOTICE);;$Pd=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($Pd||ini_get("filter.default_flags")){foreach(['_GET','_POST','_COOKIE','_SERVER']as$X){$Ol=filter_input_array(constant("INPUT$X"),FILTER_UNSAFE_RAW);if($Ol)$$X=$Ol;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");class
Server{private$params;private$key;function
__construct(array$Zh,$u=null){$this->params=$Zh;$this->key=$u;}function
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
getConfigParams(){$Zh=isset($this->params["config"])?$this->params["config"]:[];$pe=["servers"];foreach($pe
as$Yh){if(isset($Zh[$Yh]))unset($Zh[$Yh]);}return$Zh;}}class
Config{static$NavigationSimple="simple";static$NavigationDual="dual";static$NavigationReversed="reversed";private$params;private$servers=[];function
__construct(array$Zh){$this->params=$Zh;if(isset($this->params["servers"])){foreach($this->params["servers"]as$u=>$N){$Uj=new
Server($N,is_string($u)?$u:null);$this->params["servers"][$u]=$Uj;$this->servers[$Uj->getKey()]=$Uj;}}}function
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
getDefaultDriver(array$Pc){$Nc=isset($this->params["defaultDriver"])?$this->params["defaultDriver"]:null;return$Nc&&isset($Pc[$Nc])?$Nc:key($Pc);}function
getDefaultServer(){$N=isset($this->params["defaultServer"])?$this->params["defaultServer"]:null;if($N===null)return
null;$Uj=isset($this->params["servers"][$N])?$this->params["servers"][$N]:null;if($Uj)return$Uj->getKey();return$N;}function
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
getServerPairs(array$Pc){$hk=null;foreach($this->servers
as$N){if(!isset($Pc[$N->getDriver()]))continue;if(!$hk)$hk=$N->getDriver();elseif($N->getDriver()!=$hk){$hk=null;break;}}$Vj=[];foreach($this->servers
as$u=>$N){if(!isset($Pc[$N->getDriver()]))continue;$Tj=$N->getName();if($hk&&$Tj)$Vj[$u]=$Tj;else$Vj[$u]=$Pc[$N->getDriver()].($Tj!=""?" - $Tj":"");}return$Vj;}function
getServer($Sj){return
isset($this->servers[$Sj])?$this->servers[$Sj]:null;}function
applyServer($N){$N=$this->getServer($N);if(!$N)return;$this->params=array_merge($this->params,$N->getConfigParams());}private
function
parseList($fg){if(is_array($fg))return$fg;return
preg_split('~\s*,\s*~',(string)$fg);}}class
Settings{private
static$CookieName="neo_settings";static$ColorSchemeLight="light";static$ColorSchemeDark="dark";static$NavigationWidthMin=10;static$NavigationWidthMax=30;private$config;private$params=[];function
__construct(Config$Pb){$this->config=$Pb;if(isset($_COOKIE[self::$CookieName])){parse_str($_COOKIE[self::$CookieName],$this->params);$this->save();}if(isset($_COOKIE["neo_lang"])){$this->updateParameter("lang",$_COOKIE["neo_lang"]);unset($_COOKIE["neo_lang"]);cookie("neo_lang","",-3600);}}static
function
readParameter($u){parse_str(isset($_COOKIE[self::$CookieName])?$_COOKIE[self::$CookieName]:"",$Zh);return
isset($Zh[$u])?$Zh[$u]:null;}function
getParameter($u,$i=null){return
isset($this->params[$u])?$this->params[$u]:$i;}function
updateParameter($u,$Y){$this->updateParameters([$u=>$Y]);}function
updateParameters(array$Zh){$this->params=array_filter(array_merge($this->params,$Zh),function($Y){return$Y!==null;});$this->save();}private
function
save(){cookie(self::$CookieName,http_build_query($this->params),7776000);}function
getColorScheme(){return$this->getParameter("colorScheme");}function
getNavigationMode(){return($qa=$this->getParameter("navigationMode"))!==null?$qa:$this->config->getNavigationMode();}function
isNavigationSimple(){return$this->getNavigationMode()==Config::$NavigationSimple;}function
isNavigationDual(){return$this->getNavigationMode()==Config::$NavigationDual;}function
isNavigationReversed(){return$this->getNavigationMode()==Config::$NavigationReversed;}function
getNavigationWidth(){$ym=$this->getParameter("navigationWidth");if($ym===null)return
null;return
min(max((float)$ym,self::$NavigationWidthMin),self::$NavigationWidthMax);}function
isSelectionPreferred(){return($qa=$this->getParameter("preferSelection"))!==null?$qa:$this->config->isSelectionPreferred();}function
isRelationLinks(){return
isset($this->params["relationLinks"])?$this->params["relationLinks"]:$this->config->isRelationLinks();}function
getRecordsPerPage(){return($qa=$this->getParameter("recordsPerPage"))!==null?$qa:$this->config->getRecordsPerPage();}function
getEnumAsSelectThreshold(){$Y=$this->getParameter("enumAsSelectThreshold");if($Y<0)return
null;return$Y!==null?(int)$Y:$this->config->getEnumAsSelectThreshold();}}class
Hash{static
function
hkdf($v,$u,$bf="",$xj=""){if(extension_loaded("hash")&&PHP_VERSION_ID>=70120)return
hash_hkdf("sha1",$u,$v,$bf,$xj);if($xj=="")$xj=str_repeat("\0",20);$Ki=self::hmacSha1($u,$xj);$rh="";for($Cf="",$Za=1;!isset($rh[$v-1]);$Za++){$Cf=self::hmacSha1($Cf.$bf.chr($Za),$Ki);$rh
.=$Cf;}return
substr($rh,0,$v);}static
function
hmacSha1($f,$u){if(!extension_loaded("hash"))return
hash_hmac("sha1",$f,$u,true);if(strlen($u)>64)$u=sha1($u,true);$u=str_pad($u,64,"\0");$of=($u^str_repeat("\x36",64));$Ah=($u^str_repeat("\x5C",64));return
sha1($Ah.sha1($of.$f,true),true);}}class
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
\Sodium\randombytes_buf($v);$Nl=DIRECTORY_SEPARATOR==="/";if($Nl){$I=self::readDevUrandom($v);if($I!==false)return$I;}$db=$Nl&&PHP_VERSION_ID>50609&&PHP_VERSION_ID<50613;if(extension_loaded("mcrypt")&&!$db){$I=mcrypt_create_iv($v,MCRYPT_DEV_URANDOM);if($I!==false)return$I;}$eb=PHP_VERSION_ID<50444||(PHP_VERSION_ID>50500&&PHP_VERSION_ID<50528)||(PHP_VERSION_ID>50600&&PHP_VERSION_ID<50612);if(extension_loaded("openssl")&&!$eb){$I=openssl_random_pseudo_bytes($v,$yk);if($yk)return$I;}return
false;}private
static
function
readDevUrandom($v){static$m=null;if($m===null)$m=@fopen("/dev/urandom","rb");if(!$m)return
false;$hj=$v;$I="";do{$f=fread($m,$hj);if($f===false)return
false;$hj-=strlen($f);$I
.=$f;}while($hj>0);return$I;}private
static
function
readCapicom($v){$Fb=new
\COM("CAPICOM.Utilities.1");$hj=$v;$I="";do{$f=base64_decode((string)$Fb->GetRandom($v,0));$hj-=strlen($f);$I
.=$f;}while($hj>0);return$I;}private
static
function
lastResortRandom($v){static$u=null;static$xj=null;if($u===null){$f=$_SERVER;$f[]=uniqid("",true);shuffle($f);$u=sha1(serialize($f),true);if(extension_loaded("openssl"))$xj=openssl_random_pseudo_bytes(20);else{$xj="";for($q=0;$q<20;$q++)$xj
.=chr((mt_rand()^mt_rand())%256);}}else{if((ord($u)%2===0)===(ord($xj)%2===0))$u=Hash::hmacSha1($u,$xj);else$xj=Hash::hmacSha1($xj,$u);}return
Hash::hkdf($v,$u,"$v",$xj);}}if(!function_exists("str_starts_with")){function
str_starts_with($De,$Zg){return
strpos($De,$Zg)===0;}}if(!function_exists("str_contains")){function
str_contains($De,$Zg){return
strpos($De,$Zg)!==false;}}if(!function_exists("password_verify")){function
password_verify($F,$Ce){return
false;}}if(!function_exists("ini_set")){function
ini_set($Eh,$Y){return
false;}}function
version(){return
VERSION;}function
idf_unescape($Se){if(!preg_match('~^[`\'"[]~',$Se))return$Se;$Of=substr($Se,-1);return
str_replace($Of.$Of,$Of,substr($Se,1,-1));}function
q($xk){return
Connection::get()->quote($xk);}function
number($X){return
preg_replace('~[^0-9]+~','',$X);}function
number_type(){return'((?<!o)int(?!er)|numeric|real|float|double|decimal|money)';}function
remove_slashes(array$fm,$Pd=false){$J=[];foreach($fm
as$u=>$X)$J[stripslashes($u)]=(is_array($X)?remove_slashes($X,$Pd):($Pd?$X:stripslashes($X)));return$J;}function
bracket_escape($Se,$Ra=false){static$yl=[':'=>':1',']'=>':2','['=>':3','"'=>':4'];return
strtr($Se,($Ra?array_flip($yl):$yl));}function
min_version($im,$og=null,$e=null){if(!$e)$e=Connection::get();if($og&&$e->isMariaDB())$im=$og;return$im&&$e->isMinVersion($im);}function
charset(Connection$e){return($e->isMinVersion("5.5.3")?"utf8mb4":"utf8");}function
link_files($A,array$Od){switch($A){case'favicon-blue.ico':$n='favicon-blue-0f5ce53a66b1e25395d0048da369f19e__aff407a3.ico';break;case'favicon-green.ico':$n='favicon-green-def78cfa7c465c8b0e9966e3eb87407d__aff407a3.ico';break;case'favicon-orange.ico':$n='favicon-orange-cd68622e75276fdf7c60d1e9d4deee14__aff407a3.ico';break;case'favicon-purple.ico':$n='favicon-purple-d4b02fdcc3abcc374a77c65f88513c01__aff407a3.ico';break;case'favicon-red.ico':$n='favicon-red-c2ebb34a8df5aba28e15d87728a151df__aff407a3.ico';break;case'favicon-blue.svg':$n='favicon-blue-17e440832c1eac07527560a0d6f0d2ee__aff407a3.svg';break;case'favicon-green.svg':$n='favicon-green-bb254c95a033f67e3d433a3df63e160d__aff407a3.svg';break;case'favicon-orange.svg':$n='favicon-orange-53ca3b502d7fb29f01bfbf87fc4d6b24__aff407a3.svg';break;case'favicon-purple.svg':$n='favicon-purple-4cfd57d31ab991e8071fe34060cd3123__aff407a3.svg';break;case'favicon-red.svg':$n='favicon-red-a006e401273230fd6be80568c8361b57__aff407a3.svg';break;case'apple-touch-icon-blue.png':$n='apple-touch-icon-blue-f2a5f6f50418d7293b806faf273fe381__aff407a3.png';break;case'apple-touch-icon-green.png':$n='apple-touch-icon-green-903cc109ea077cd9e91508416c5e335a__aff407a3.png';break;case'apple-touch-icon-orange.png':$n='apple-touch-icon-orange-6efda14fd1d3c45382c67d7f324bdccf__aff407a3.png';break;case'apple-touch-icon-purple.png':$n='apple-touch-icon-purple-2388fa66883b7c5e6b4cf5c795eae8fc__aff407a3.png';break;case'apple-touch-icon-red.png':$n='apple-touch-icon-red-507228751d2170d047e72142d2c02390__aff407a3.png';break;case'logo.svg':$n='logo-de272eb4bdca9c6fffd38c073270fb1a__9d7e398f.svg';break;case'jush.css':$n='jush-b3a93b18444da26820ff61746521dede__72e4fe51.css';break;case'jush-dark.css':$n='jush-dark-f8dac59c6ad1018686e52a0e0357e421__2ec7793c.css';break;case'jush.js':$n='jush-615bc0b9720a1de8edd2c6876a3495b6__aab91337.js';break;case'icons.svg':$n='icons-70163a2695280bf75edba563e7b5471b__2ec7793c.svg';break;case'default-blue.css':$n='default-blue-564b3ff62703b0741b8754503c621af3__cfb00ea1.css';break;case'default-green.css':$n='default-green-8facfae54345a3eb358848ed4141060f__cfb00ea1.css';break;case'default-orange.css':$n='default-orange-4fd2276ffa8eaad143aec2dba3782911__3402276c.css';break;case'default-purple.css':$n='default-purple-33d1c33b271b014ef4b3f2f4e42cd9f9__3402276c.css';break;case'default-red.css':$n='default-red-9c7de6d1d78ea798bfef943c92b6b611__cfb00ea1.css';break;case'default-blue-dark.css':$n='default-blue-dark-79895bd8e65cadab7d67d31c191a833d__7a7f64b1.css';break;case'default-green-dark.css':$n='default-green-dark-d7e561f7fc07f913992951110461fd8c__7a7f64b1.css';break;case'default-orange-dark.css':$n='default-orange-dark-e6668a1545546a87b40acb95390b5283__3549fa11.css';break;case'default-purple-dark.css':$n='default-purple-dark-83c0052a3d8e86dfb6debf8349377b25__3549fa11.css';break;case'default-red-dark.css':$n='default-red-dark-aa471f32fb495651c17bba291cd8b147__7a7f64b1.css';break;case'main.js':$n='main-eaf2ce2c3d91edbef355936903e47e59__45ca58f9.js';break;default:$n=null;break;}if(!$n)return
null;return
BASE_URL."?file=".urldecode($n);}function
ini_bool($Eh){$X=ini_get($Eh);return
preg_match('~^(on|true|yes)$~i',$X)||(int)$X;}function
ini_bytes($df){$X=ini_get($df);switch(strtolower(substr($X,-1))){case'g':$X=(int)$X*1024;case'm':$X=(int)$X*1024;case'k':$X=(int)$X*1024;}return$X;}function
sid(){static$J;if($J===null)$J=(session_id()&&!($_COOKIE&&ini_bool("session.use_cookies")));return$J;}function
save_driver_name($Nc,$N,$A){restart_session();$_SESSION["drivers"][$Nc][$N]=$A;stop_session();}function
get_driver_name($Nc,$N=null){return
isset($_SESSION["drivers"][$Nc][$N])?$_SESSION["drivers"][$Nc][$N]:Drivers::get($Nc);}function
save_login($Nc,$N,$V,$F,$h=""){$u=isset($_COOKIE["neo_key"])?$_COOKIE["neo_key"]:null;$_SESSION["pwds"][$Nc][$N][$V]=$u?[encrypt_string($F,$u)]:$F;$_SESSION["db"][$Nc][$N][$V][$h]=true;}function
delete_login($Nc,$N,$V){unset($_SESSION["pwds"][$Nc][$N][$V]);unset($_SESSION["db"][$Nc][$N][$V]);}function
get_password(){$F=get_session("pwds");if(is_array($F))return$_COOKIE["neo_key"]?decrypt_string($F[0],$_COOKIE["neo_key"]):false;return$F;}function
get_vals($H,$b=0){$J=[];$I=Connection::get()->query($H);if(is_object($I)){while($K=$I->fetchRow())$J[]=$K[$b];}return$J;}function
get_key_vals($H,$e=null,$ck=true){if(!$e)$e=Connection::get();$J=[];$I=$e->query($H);if(is_object($I)){while($K=$I->fetchRow()){if($ck)$J[$K[0]]=$K[1];else$J[]=$K[0];}}return$J;}function
get_rows($H,$e=null,$j="<p class='error'>"){if(!$e)$e=Connection::get();$J=[];$I=$e->query($H);if(is_object($I)){while($K=$I->fetchAssoc())$J[]=$K;}elseif(!$I&&!is_object($e)&&$j&&(defined("AdminNeo\PAGE_HEADER")||$j=="-- "))echo$j.error()."\n";return$J;}function
unique_array(array$K,array$t){foreach($t
as$s){if(!preg_match("~PRIMARY|UNIQUE~",$s["type"])&&!$s["partial"])continue;$Kl=[];foreach($s["columns"]as$u){if(!isset($K[$u]))continue
2;$Kl[$u]=$K[$u];}return$Kl;}return
null;}function
escape_key($u){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$u,$y))return$y[1].idf_escape(idf_unescape($y[2])).$y[3];return
idf_escape($u);}function
where($Z,$l=[]){$Ob=[];foreach((array)$Z["where"]as$u=>$X){$u=bracket_escape($u,true);$b=escape_key($u);$Kd=isset($l[$u]["type"])?$l[$u]["type"]:null;$je=isset($l[$u]["full_type"])?$l[$u]["full_type"]:null;if(DIALECT=="sql"&&$Kd=="json")$Ob[]="$b = CAST(".q($X)." AS JSON)";elseif(DIALECT=="pgsql"&&preg_match('~^jsonb?$~',$je))$Ob[]="$b::jsonb = ".q($X)."::jsonb";elseif(DIALECT=="sql"&&is_numeric($X)&&strpos($X,".")!==false)$Ob[]="$b LIKE ".q($X);elseif(DIALECT=="mssql"&&strpos($Kd,"datetime")===false)$Ob[]="$b LIKE ".q(preg_replace('~[_%[]~','[\0]',$X));else$Ob[]="$b = ".(isset($l[$u])?unconvert_field($l[$u],q($X)):q($X));if(DIALECT=="sql"&&preg_match('~char|text~',$Kd)&&preg_match("~[^ -@]~",$X))$Ob[]="$b = ".q($X)." COLLATE ".charset(Connection::get())."_bin";}foreach((array)$Z["null"]as$u)$Ob[]=escape_key($u)." IS NULL";return
implode(" AND ",$Ob);}function
where_check($X,$l=[]){parse_str($X,$ob);remove_slashes([&$ob]);return
where($ob,$l);}function
where_link($q,$b,$Y,$Bh="="){return"&where%5B$q%5D%5Bcol%5D=".urlencode($b)."&where%5B$q%5D%5Bop%5D=".urlencode(($Y!==null?$Bh:"IS NULL"))."&where%5B$q%5D%5Bval%5D=".urlencode($Y);}function
convert_fields(array$c,array$l,array$M=[]){$I="";foreach($c
as$u=>$X){if($M&&!in_array(idf_escape($u),$M))continue;$Ja=convert_field($l[$u]);if($Ja)$I
.=", $Ja AS ".idf_escape($u);}return$I;}function
cookie_path(){return
strtr(preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),[";"=>"%3B",","=>"%2C"]);}function
cookie($A,$Y,$Yf=2592000){header("Set-Cookie: $A=".rawurlencode($Y).($Yf?"; expires=".gmdate("D, d M Y H:i:s",time()+$Yf)." GMT":"")."; path=".cookie_path().(HTTPS?"; secure":"")."; HttpOnly; SameSite=lax",false);}function
get_url($Tl,$Vb){$J=@file_get_contents($Tl,false,$Vb);if(function_exists('http_get_last_response_headers'))$http_response_header=($qa=http_get_last_response_headers())!==null?$qa:[];return[$J,isset($http_response_header)?$http_response_header:[]];}function
get_settings($Xb="neo_settings"){parse_str(isset($_COOKIE[$Xb])?$_COOKIE[$Xb]:"",$P);return$P;}function
get_setting($u,$Xb="neo_settings"){$P=get_settings($Xb);return
isset($P[$u])?$P[$u]:null;}function
save_settings(array$P,$Xb="neo_settings"){cookie($Xb,http_build_query($P+get_settings($Xb)));}function
restart_session(){if(!ini_bool("session.use_cookies")&&session_status()==PHP_SESSION_NONE)session_start();}function
stop_session($Xd=false){$Xl=ini_bool("session.use_cookies");if(!$Xl||$Xd){session_write_close();if($Xl&&ini_set("session.use_cookies","0")===false)session_start();}}function&get_session($u){return$_SESSION[$u][DRIVER][SERVER][$_GET["username"]];}function
set_session($u,$X){$_SESSION[$u][DRIVER][SERVER][$_GET["username"]]=$X;}function
auth_url($hm,$N,$V,$h=null){$Sl=remove_from_uri(implode("|",array_keys(Drivers::getList()))."|username|ext|".($h!==null?"db|":"").($hm=='mssql'||$hm=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$Sl,$y);return"$y[1]?".(sid()?session_name()."=".urlencode(session_id())."&":"").urlencode($hm)."=".urlencode($N)."&".($_GET["ext"]?"ext=".urlencode($_GET["ext"])."&":"")."username=".urlencode($V).($h!=""?"&db=".urlencode($h):"").($y[2]?"&$y[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($lg,$_=null){if($_!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($lg!==null?$lg:$_SERVER["REQUEST_URI"]))][]=$_;}if($lg!==null){if($lg=="")$lg=".";header("Location: $lg");exit;}}function
query_redirect($H,$lg,$_,$Yi=true,$sd=true,$Cd=false,$nl=""){if($sd){$sk=microtime(true);$Cd=!Connection::get()->query($H);$nl=format_time($sk);}$ok=$H?Admin::get()->formatMessageQuery($H,$nl,$Cd):"";if($Cd){Admin::get()->addError(error().$ok.script("initToggles();"));return
false;}if($Yi)redirect($lg,$_.$ok);return
true;}function
queries_redirect($lg,$_,$Yi){$Pi=implode("\n",Queries::$queries);$nl=format_time(Queries::$start);return
query_redirect($Pi,$lg,$_,$Yi,false,!$Yi,$nl);}class
Queries{static$queries=[];static$start=0.0;}function
queries($H){if(!Queries::$start)Queries::$start=microtime(true);if(support("sql")){Queries::$queries[]=(preg_match('~;$~',$H)?"DELIMITER ;;\n$H;\nDELIMITER ":$H).";";return
Connection::get()->query($H);}else{Queries::$queries[]=$H;return[];}}function
apply_queries($H,array$S,$nd='AdminNeo\table'){foreach($S
as$Q){if(!queries("$H ".$nd($Q)))return
false;}return
true;}function
format_time($sk){return
lang(100,max(0,microtime(true)-$sk));}function
relative_uri(){return
str_replace(":","%3a",preg_replace('~^[^?]*/([^?]*)~','\1',$_SERVER["REQUEST_URI"]));}function
remove_from_uri($Yh=""){return
substr(preg_replace("~(?<=[?&])($Yh".(sid()?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_file($u,$pc=false,$wc=""){$m=$_FILES[$u];if(!$m)return
null;foreach($m
as$u=>$X)$m[$u]=(array)$X;$J='';foreach($m["error"]as$u=>$j){if($j)return$j;$A=$m["name"][$u];$tl=$m["tmp_name"][$u];$Tb=file_get_contents($pc&&preg_match('~\.gz$~',$A)?"compress.zlib://$tl":$tl);if($pc){$sk=substr($Tb,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$sk))$Tb=iconv("utf-16","utf-8",$Tb);elseif($sk=="\xEF\xBB\xBF")$Tb=substr($Tb,3);}if($wc){if(!preg_match("~$wc\\s*\$~",$Tb))$Tb
.=";";$Tb
.="\n\n";}$J
.=$Tb;}return$J;}function
upload_error($j){$vg=($j==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($j?lang(101).($vg?" ".lang(102,$vg):""):lang(103));}function
repeat_pattern($oi,$v){return
str_repeat("$oi{0,65535}",$v/65535)."$oi{0,".($v%65535)."}";}function
is_utf8($X){return(preg_match('~~u',$X)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$X));}function
format_number($X){return
strtr(number_format($X,0,".",lang(104)),preg_split('~~u',lang(105),-1,PREG_SPLIT_NO_EMPTY));}function
friendly_url($X){return
preg_replace('~\W~i','-',$X);}function
table_status1($Q,$Ed=false){$J=table_status($Q,$Ed);return($J?reset($J):["Name"=>$Q]);}function
column_foreign_keys($Q){$J=[];foreach(Admin::get()->getForeignKeys($Q)as$o){foreach($o["source"]as$X)$J[$X][]=$o;}return$J;}function
fields_from_edit(){$J=[];foreach((array)$_POST["field_keys"]as$u=>$X){if($X!=""){$X=bracket_escape($X);$_POST["function"][$X]=$_POST["field_funs"][$u];$_POST["fields"][$X]=$_POST["field_vals"][$u];}}foreach((array)$_POST["fields"]as$u=>$X){$A=bracket_escape($u,true);$J[$A]=["field"=>$A,"full_type"=>"varchar","type"=>"varchar","privileges"=>["insert"=>1,"update"=>1,"where"=>1,"order"=>1],"null"=>true,"auto_increment"=>($u==Driver::get()->primary),];}return$J;}function
dump_headers($Qe,$Rg=false){$Qe=friendly_url($Qe).date("-Ymd-His");$zd=Admin::get()->sendDumpHeaders($Qe,$Rg);$Uh=$_POST["output"];if($Uh!="text")header("Content-Disposition: attachment; filename=$Qe.$zd".($Uh!="file"&&preg_match('~^[0-9a-z]+$~',$Uh)?".$Uh":""));session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$zd;}function
dump_table_order(array$Xg,array$ej){$Hf=array_flip($Xg);$Ih=[];$qm=[];$hc=false;$pm=function($A)use(&$pm,&$Ih,&$qm,&$hc,$Hf,$ej){if(isset($Ih[$A]))return;if(isset($qm[$A])){$hc=true;return;}$qm[$A]=true;foreach(isset($ej[$A])?$ej[$A]:[]as$cj){if(isset($Hf[$cj]))$pm($cj);}unset($qm[$A]);$Ih[$A]=true;};foreach($Xg
as$A)$pm($A);return($hc?null:array_keys($Ih));}function
dump_csv($K){$El=$_POST["format"]=="tsv";foreach($K
as$u=>$X){if(preg_match('~["\n]|^0[^.]|\.\d*0$|'.($El?'\t':'[,;]|^$').'~',$X))$K[$u]='"'.str_replace('"','""',$X).'"';}echo
implode(($_POST["format"]=="csv"?",":($El?"\t":";")),$K)."\r\n";}function
apply_sql_function($p,$b){return($p?($p=="unixepoch"?"DATETIME($b, '$p')":($p=="count distinct"?"COUNT(DISTINCT ":strtoupper("$p("))."$b)"):$b);}function
get_temp_dir(){$mi=ini_get("upload_tmp_dir");if(!$mi)$mi=sys_get_temp_dir();return$mi;}function
open_file_with_lock($n){if(is_link($n))return
null;$m=@fopen($n,"c+");if(!$m)return
null;@chmod($n,0660);if(!flock($m,LOCK_EX)){fclose($m);return
null;}return$m;}function
write_and_unlock_file($m,$f){rewind($m);fwrite($m,$f);ftruncate($m,strlen($f));unlock_file($m);}function
unlock_file($m){flock($m,LOCK_UN);fclose($m);}function
first(array$Ia){return
reset($Ia);}function
get_private_key($Zb){$n=get_temp_dir()."/adminneo.key";if(!$Zb&&!file_exists($n))return
false;$m=open_file_with_lock($n);if(!$m)return
false;$u=stream_get_contents($m);if(!$u){$u=Random::strongKey();write_and_unlock_file($m,$u);}else
unlock_file($m);return$u;}function
get_random_string(){return
Random::strongKey();}function
select_value($X,$x,$k,$jl){if(is_array($X)){$J="";if(array_filter($X,'is_array')==array_values($X)){$Df=[];foreach($X
as$W)$Df+=array_fill_keys(array_keys($W),null);foreach(array_keys($Df)as$zf)$J
.="<th>".h($zf);foreach($X
as$W){$J
.="<tr>";foreach(array_merge($Df,$W)as$cm)$J
.="<td>".select_value($cm,$x,$k,$jl);}}else{foreach($X
as$zf=>$W)$J
.="<tr>".($X!=array_values($X)?"<th>".h($zf):"")."<td>".select_value($W,$x,$k,$jl);}return"<table>$J</table>";}$Bj="";if($k&&$X!==null&&($jl===null||strlen($X)<=$jl)&&($fm=Driver::get()->explodeArrayValue($X,$k["full_type"],$Bj))){$Aj=$k;$Aj["type"]=$Aj["full_type"]=$Bj;$J=select_array_value($fm,$X,$x,$Aj,$jl);return
Driver::get()->implodeArrayValues($J,$k["full_type"]);}if(!$x)$x=Admin::get()->getFieldValueLink($X,$k);if($k)$X=Connection::get()->formatValue($X,$k);$J=$k?Admin::get()->formatFieldValue($X,$k):$X;if($J!==null){if(!is_utf8($J))$J="\0";elseif($jl!=""&&is_shortable($k))$J=truncate_utf8($J,max(0,+$jl));else$J=h($J);}return
Admin::get()->formatSelectionValue($J,$x,$k,$X);}function
select_array_value(array$fm,$X,$x,array$k,$jl){$I=[];foreach($fm
as$Y){if(is_array($Y))$I[]=select_array_value($Y,$X,$x,$k,$jl);else{$If=preg_replace('~(where%5B\d+%5D%5Bval%5D=)'.preg_quote(urlencode($X),"~")."~",'${1}'.urlencode($Y),$x);$I[]=select_value($Y,$If,$k,$jl);}}return$I;}function
is_blob(array$k){$Hl=Driver::get()->getStructuredTypes();$U=lang(106);return
preg_match('~blob|bytea|raw|file~',$k["type"])&&!in_array($k["type"],isset($Hl[$U])?$Hl[$U]:[]);}function
is_mail($Y){return
is_string($Y)&&filter_var($Y,FILTER_VALIDATE_EMAIL);}function
is_web_url($Y){if(!is_string($Y)||!preg_match('~^(https?:)?//~i',$Y))return
false;$Mb=parse_url($Y);if(!$Mb)return
false;$Tl=$Y;if(isset($Mb['path'])){$fd=array_map('urlencode',explode('/',$Mb['path']));$Tl=str_replace($Mb['path'],implode('/',$fd),$Tl);}if(isset($Mb['query'])){parse_str($Mb['query'],$Zh);$Tl=str_replace($Mb['query'],http_build_query($Zh),$Tl);}if(!isset($Mb['scheme']))$Tl="https:$Tl";return(bool)filter_var($Tl,FILTER_VALIDATE_URL);}function
is_shortable($k){return$k&&!preg_match('~'.number_type().'|date|time|year~',$k["type"]);}function
host_port($N){return(preg_match('~^(:([^:].*)|(\[(.+)]|(([^:]+://)?[^:]+))(:(\d+))?)$~',$N,$y)?[(isset($y[4])?$y[4]:"").(isset($y[5])?$y[5]:""),$y[2].(isset($y[8])?$y[8]:"")]:[$N,'']);}function
count_rows($Q,$Z,$qf,$te){$H=" FROM ".table($Q).($Z?" WHERE ".implode(" AND ",$Z):"");return($qf&&(DIALECT=="sql"||count($te)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$te).")$H":"SELECT COUNT(*)".($qf?" FROM (SELECT 1$H GROUP BY ".implode(", ",$te).") x":$H));}function
slow_query($H){$h=Admin::get()->getDatabase();$ol=Admin::get()->getQueryTimeout();$jk=Driver::get()->slowQuery($H,$ol);$e=null;if(!$jk&&support("kill")){$e=connect();if($e&&($h==""||$e->selectDatabase($h))){$Ff=$e->getValue(connection_id());echo'<script',nonce(),'>
	const timeout = setTimeout(() => {
		ajax(\'',js_escape(ME),'script=kill\', function() {
		}, \'kill=',$Ff,'&token=',get_token(),'\');
	}, ',1000*$ol,');
</script>
';}}ob_flush();flush();$J=@get_key_vals(($jk?:$H),$e,false);if($e){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$J;}function
get_token(){$Ui=rand(1,1e6);return($Ui^$_SESSION["token"]).":$Ui";}function
verify_token(){list($ul,$Ui)=explode(":",$_POST["token"]);return($Ui^$_SESSION["token"])==$ul&&in_array($_SERVER["HTTP_SEC_FETCH_SITE"],["","same-origin"]);}function
script($lk,$xl="\n"){return"<script".nonce().">$lk</script>$xl";}function
script_src($Tl,$tc=false){return"<script src='".h($Tl)."'".nonce().($tc?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
input_hidden($A,$Y=""){return"<input type='hidden' name='".h($A)."' value='".h($Y)."'>";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($xk){if($xk===null||$xk==="")return"";return
str_replace(["&","<","\"","'","\0"],["&amp;","&lt;","&quot;","&#039;","&#0;"],$xk);}function
truncate_utf8($xk,$v=80){if($xk=="")return"";if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$v).")($)?)u",$xk,$y))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$v).")($)?)",$xk,$y);return
h($y[1]).(isset($y[2])?"":"<i>…</i>");}function
icon_solo($r){return
icon($r,"solo");}function
icon_chevron_down(){return
icon("chevron-down","chevron");}function
icon_chevron_right(){return
icon("chevron-down","chevron-right");}function
icon($r,$wb=null){$r=h($r);return"<svg class='icon ic-$r $wb'><use href='".link_files("icons.svg",[])."#$r'/></svg>";}function
checkbox($A,$Y,$rb,$Jf="",$zh="",$wb="",$Lf=""){$J="<input type='checkbox' name='$A' value='".h($Y)."'".($rb?" checked":"").($Lf?" aria-labelledby='$Lf'":"").">".($zh?script("qsl('input').onclick = function () { $zh };",""):"");return($Jf!=""||$wb?"<label".($wb?" class='$wb'":"").">$J".h($Jf)."</label>":$J);}function
optionlist($C,$Mj=null,$Yl=false){$J="";foreach($C
as$zf=>$W){$Gh=[$zf=>$W];if(is_array($W)){$J
.='<optgroup label="'.h($zf).'">';$Gh=$W;}foreach($Gh
as$u=>$X)$J
.='<option'.($Yl||is_string($u)?' value="'.h($u).'"':'').($Mj!==null&&($Yl||is_string($u)?(string)$u:$X)===$Mj?' selected':'').'>'.h($X);if(is_array($W))$J
.='</optgroup>';}return$J;}function
html_select($A,$C,$Y="",$yh="",$Lf="",$Yl=false){static$Jf=0;$Kf="";if(!$Lf&&substr(isset($C[""])?$C[""]:"",0,1)=="("){$Jf++;$Lf="label-$Jf";$Kf="<option value='' id='$Lf'>".h($C[""]);unset($C[""]);}return"<select name='".h($A)."'".($Lf?" aria-labelledby='$Lf'":"").">".$Kf.optionlist($C,$Y,$Yl)."</select>".($yh?script("qsl('select').onchange = function () { $yh };",""):"");}function
html_radios($A,$C,$Y=""){$I="<span class='labels'>";foreach($C
as$u=>$X)$I
.="<label><input type='radio' name='".h($A)."' value='".h($u)."'".($u==$Y?" checked":"").">".h($X)."</label>";$I
.="</span>";return$I;}function
confirm($_="",$Oj="qsl('input')"){return
script("$Oj.onclick = () => confirm('".($_?js_escape($_):lang(107))."');","");}function
print_fieldset_start($r,$Uf,$Pe,$nm=false,$kk=false){echo"<fieldset id='fieldset-$r' class='closable ".(!$nm?" closed":"")."'>","<legend><a href='#'>$Uf</a></legend>",icon($Pe,"fieldset-icon jsonly"),"<div class='fieldset-content".($kk?" sortable":"")."'>";}function
print_fieldset_end($r,$kk=false){echo"</div>",script("initFieldset('$r');","");if($kk)echo
script("initSortable('#fieldset-$r .fieldset-content');","");echo"</fieldset>\n";}function
bold($ab,$wb=""){return($ab?" class='$wb active'":($wb?" class='$wb'":""));}function
js_escape($xk){return
addcslashes($xk,"\r\n'\\/");}function
js_escape_key($xk){return'"'.addcslashes($xk,"\r\n\t\"\\/").'"';}function
pagination($E,$ec){return"<li>".($E==$ec?"<strong>".($E+1)."</strong>":'<a href="'.h(remove_from_uri("page").($E?"&page=$E".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($E+1)."</a>")."</li>";}function
print_hidden_fields(array$Li,array$Te=[],$Ci=""){$I=false;foreach($Li
as$u=>$X){if(!in_array($u,$Te)){if(is_array($X))print_hidden_fields($X,[],$u);else{$I=true;echo
input_hidden($Ci?$Ci."[$u]":$u,$X);}}}return$I;}function
hidden_fields_get(){if(sid())echo
input_hidden(session_name(),session_id());if(SERVER!==null)echo
input_hidden(DRIVER,SERVER);echo
input_hidden("username",$_GET["username"]);}function
enum_input($Ka,array$k,$Y,$dd=null,$qb=false){preg_match_all("~'((?:[^']|'')*)'~",$k["length"],$z);$fm=$z[1];$ml=Admin::get()->getSettings()->getEnumAsSelectThreshold();$M=!$qb&&$ml!==null&&count($fm)>$ml;$U=$qb?"checkbox":"radio";$wa=$M?"selected":"checked";$I=$M?"<select $Ka>":"<span class='labels'>";if($M&&$k["null"]&&$dd!==""){$rb=$Y===null?$wa:"";$I
.="<option value='__adminneo_empty__' disabled $rb></option>";}if($dd!==null){$rb=(is_array($Y)?in_array($dd,$Y):$Y===$dd)?$wa:"";if($M)$I
.="<option value='$dd' $rb>".lang(108)."</option>";else$I
.="<label><input type='$U' $Ka value='$dd' $rb><i>".lang(108)."</i></label>";}foreach($fm
as$X){if($dd===""&&$X==="")continue;$X=stripcslashes(str_replace("''","'",$X));$rb=is_array($Y)?in_array($X,$Y):$Y===$X;$rb=$rb?$wa:"";$ee=$X===""?("<i>".lang(108)."</i>"):h(Admin::get()->formatFieldValue($X,$k));if($M)$I
.="<option value='".h($X)."' $rb>$ee</option>";else$I
.=" <label><input type='$U' $Ka value='".h($X)."' $rb>$ee</label>";}$I
.=$M?"</select>":"</span>";return$I;}function
input($k,$Y,$p,$Oa=false){$A=h(bracket_escape($k["field"]));$Hl=Driver::get()->getTypes();$rf=isset($k["full_type"])&&Admin::get()->detectJson($k["full_type"],$Y,true);$jj=(DIALECT=="mssql"&&$k["auto_increment"]&&!$_POST["clone"]);if($jj&&!$_POST["save"])$p=null;if(in_array($k["type"],Driver::get()->getUserTypes())){$kd=type_values($Hl[$k["type"]]);if($kd){$k["type"]="enum";$k["length"]=$kd;}}$Ka=" name='fields[$A]' ".($Oa?" autofocus":"");$le=(isset($_GET["select"])||$jj?["orig"=>lang(109)]:[])+Admin::get()->getFieldFunctions($k);$Be=(in_array($p,$le)||isset($le[$p]));echo"<td class='function'>",Driver::get()->getUnconvertFunction($k)." ";if(count($le)>1){$Mj=$p===null||$Be?$p:"";echo"<select name='function[$A]'>".optionlist($le,$Mj)."</select>",help_script_command("value.replace(/^SQL\$/, '')",true),script("qsl('select').onchange = functionChange;","");}else
echo
h(reset($le));echo"</td><td>";$ef=Admin::get()->getFieldInput(isset($_GET["edit"])?$_GET["edit"]:null,$k,$Ka,$Y,$p);if($ef!="")echo$ef;elseif(preg_match('~bool~',$k["type"]))echo"<input type='hidden'$Ka value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$Y)?" checked='checked'":"")."$Ka value='1'>";elseif($k["type"]=="enum")echo
enum_input($Ka,$k,$Y);elseif($k["type"]=="set"){preg_match_all("~'((?:[^']|'')*)'~",$k["length"],$z);echo"<span class='labels'>";foreach($z[1]as$X){$X=stripcslashes(str_replace("''","'",$X));$rb=$Y!==null&&in_array($X,explode(",",$Y),true);$rb=$rb?"checked":"";$ee=$X===""?("<i>".lang(108)."</i>"):h(Admin::get()->formatFieldValue($X,$k));echo" <label><input type='checkbox' name='fields[$A][]' value='".h($X)."' $rb>$ee</label>";}echo"</span>";}elseif(is_blob($k)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$A'>";elseif($rf)echo"<textarea $Ka cols='50' rows='12' class='jush-json'>".h($Y).'</textarea>';elseif(($hl=preg_match('~text|lob|memo|json~i',$k["type"]))||preg_match("~\n~",$Y)){if($hl&&DIALECT!="sqlite")$Ka
.=" cols='50' rows='12'";else{$L=min(12,substr_count($Y,"\n")+1);$Ka
.=" cols='30' rows='$L'";}echo"<textarea $Ka>".h($Y).'</textarea>';}else{$yg=!preg_match('~int~',$k["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$k["length"],$y)?((preg_match("~binary~",$k["type"])?2:1)*$y[1]+($y[3]?1:0)+($y[2]&&!$k["unsigned"]?1:0)):($Hl&&$Hl[$k["type"]]?$Hl[$k["type"]]+($k["unsigned"]?0:1):0);if(DIALECT=='sql'&&Connection::get()->isMinVersion("5.6")&&preg_match('~time~',$k["type"]))$yg+=7;echo"<input class='input'".((!$Be||$p==="")&&preg_match('~(?<!o)int(?!er)~',$k["type"])&&!preg_match('~\[\]~',$k["full_type"])?" type='number'":"").($p!="now"?" value='".h($Y)."'":" data-last-value='".h($Y)."'").($yg?" data-maxlength='$yg'":"").(preg_match('~char|binary~',$k["type"])&&$yg>20?" size='44'":"")."$Ka>";}$Ge=Admin::get()->getFieldInputHint($_GET["edit"],$k,$Y);if($Ge!="")echo" <span class='input-hint'>$Ge</span>";$Td=0;foreach($le
as$u=>$X){if($u===""||!$X)break;$Td++;}if(count($le)>1)echo
script("qsl('td').oninput = partial(skipOriginal, $Td);");}function
process_input($k){$Se=bracket_escape($k["field"]);$p=isset($_POST["function"][$Se])?$_POST["function"][$Se]:"";if($p=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?idf_escape($k["field"]):false);if($p=="NULL")return
Driver::get()->getNull();if(is_blob($k)&&ini_bool("file_uploads")){$m=get_file("fields-$Se");if(!is_string($m))return
false;return
Driver::get()->quoteBinary($m);}$Y=isset($_POST["fields"][$Se])?$_POST["fields"][$Se]:(isset($_FILES["fields"]["name"][$Se])?$_FILES["fields"]["name"][$Se]:null);if($Y===null)return
false;if($k["auto_increment"]&&$Y=="")return
null;if($k["type"]=="set")$Y=implode(",",(array)$Y);if($p=="json"){$Y=json_decode($Y,true);if(!is_array($Y))return
false;return$Y;}return
Admin::get()->processFieldInput($k,$Y,$p);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$oj=$md=[];foreach(table_status("",true)as$Q=>$R){$Rk=Admin::get()->getTableName($R);if(!isset($R["Engine"])||$Rk==""||($_POST["tables"]&&!in_array($Q,$_POST["tables"])))continue;$I=Connection::get()->query("SELECT".limit("1 FROM ".table($Q)," WHERE ".implode(" AND ",Admin::get()->processSelectionSearch(fields($Q),[])),1));if($I&&!$I->fetchRow())continue;$x=h(ME."select=".urlencode($Q)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]));if($I)$oj[]="<li><a href='$x'>".icon("search")."$Rk</a></li>";else$md[]="<div class='error'><a href='$x'>$Rk</a>: ".error()."</div>";}if($oj)echo"<ul class='links'>\n",implode("\n",$oj),"</ul>\n";if($md)echo
implode("\n",$md),"\n";if(!$oj&&!$md)echo"<p class='message'>".lang(78)."</p>\n";}function
help_script($hl,$gk=false){return
script("initHelpFor(qsl('select, input'), '".h($hl)."', $gk);","");}function
help_script_command($Gb,$gk=false){return
script("initHelpFor(qsl('select, input'), (value) => { return $Gb; }, $gk);","");}function
edit_form($Q,$l,$K,$Rl){$Rk=Admin::get()->getTableName(table_status1($Q,true));$T=$Rl?lang(38):lang(110);page_header("$T: $Rk",["select"=>[$Q,$Rk],$T]);if($K===false){echo"<p class='error'>".lang(88)."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";$Zc=false;if(!$l)echo"<p class='error'>".lang(111)."\n";else{echo"<table class='box'>".script("qsl('table').onkeydown = onEditingKeydown;");$Oa=!$_POST;foreach($l
as$A=>$k){echo"<tr><th>".Admin::get()->getFieldName($k);$u=bracket_escape($A);$i=isset($_GET["set"][$u])?$_GET["set"][$u]:null;if($i===null){$i=$k["default"];if($k["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$i,$gj))$i=$gj[1];if(DIALECT=="sql"&&preg_match('~binary~',$k["type"]))$i=bin2hex($i);}$Y=($K!==null?($K[$A]!=""&&DIALECT=="sql"&&preg_match("~enum|set~",$k["type"])&&is_array($K[$A])?implode(",",$K[$A]):(is_bool($K[$A])?+$K[$A]:$K[$A])):(!$Rl&&$k["auto_increment"]?"":(isset($_GET["select"])?false:$i)));if(!$_POST["save"]&&is_string($Y))$Y=Admin::get()->formatFieldValue($Y,$k);if(($Rl&&!isset($k["privileges"]["update"]))||$k["generated"]){echo"<td class='function'></td><td>";if($Rl||!$k["generated"])echo
select_value($Y,'',$k,null);else
echo"<code class='jush-".DIALECT."'>",h($Y),"</code>";echo"</td>";}else{$Zc=true;$p=($_POST["save"]?isset($_POST["function"][$A])?$_POST["function"][$A]:"":($Rl&&preg_match('~^CURRENT_TIMESTAMP~i',$k["on_update"])?"now":($Y===false?null:($Y!==null?'':'NULL'))));if(!$_POST&&!$Rl&&$Y==$k["default"]&&preg_match('~^[\w.]+\(~',$Y))$p="SQL";if(preg_match("~time~",$k["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$Y)){$Y="";$p="now";}if($k["type"]=="uuid"&&$Y=="uuid()"){$Y="";$p="uuid";}if($Oa!==false)$Oa=($k["auto_increment"]||$p=="now"||$p=="uuid"?null:true);input($k,$Y,$p,(bool)$Oa);if($Oa)$Oa=false;}echo"\n";}if(!support("table")&&!fields($Q))echo"<tr>"."<th><input class='input' name='field_keys[]'>".script("qsl('input').oninput = fieldChange;","")."<td class='function'>".html_select("field_funs[]",Admin::get()->getFieldFunctions(["null"=>isset($_GET["select"])]))."<td><input class='input' name='field_vals[]'>"."\n";echo"</table>\n",script("initToggles(gid('form'));");}echo"<p>";if($Zc){echo"<input type='submit' class='button default' value='".lang(112)."'>\n";if(!isset($_GET["select"]))echo"<input type='submit' class='button' name='insert' value='".($Rl?lang(113):lang(114))."' title='Ctrl+Shift+Enter'>\n",($Rl?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".lang(115)."…', this); };"):"");}echo($Rl?"<input type='submit' class='button' name='delete' value='".lang(116)."'>".confirm()."\n":"");if(isset($_GET["select"]))print_hidden_fields(["check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]]);echo
input_hidden("referer",isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"]),input_hidden("save","1"),input_token(),"</form>\n";}function
file_upload_form_script($be,$ff){$qg=ini_get("max_file_uploads");$vg=ini_get("upload_max_filesize");$wg=ini_bytes("upload_max_filesize");return
script("initFilesUploadForm('".js_escape($be)."', '".js_escape($ff)."', "."$qg, '".lang(117,$qg,"\'max_file_uploads\'")."', "."$wg, '".lang(118,$vg,"\'upload_max_filesize\'")."')");}function
compress_alphabet(){return
strtr(implode(range('"','~')),"'\\","!\n");}function
decompress_string($xk){$Ea=array_flip(str_split(compress_alphabet()));$v=strlen($xk);$em=($v?13*($v-1)/2-$Ea[$xk[0]]:0);$Wa="";$mj=0;$nj=0;for($q=1;$q<$v;$q+=2){$mj=($mj<<13)+$Ea[$xk[$q]]*93+$Ea[$xk[$q+1]];$nj+=13;while($nj>=8&&$em>=8){$nj-=8;$em-=8;$Wa
.=chr($mj>>$nj);$mj&=(1<<$nj)-1;}}if($Wa=="")return"";return
function_exists('gzinflate')?gzinflate($Wa):inflate($Wa);}function
inflate($Wa){$Vf=[3,4,5,6,7,8,9,10,11,13,15,17,19,23,27,31,35,43,51,59,67,83,99,115,131,163,195,227,258];$Wf=[0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,3,3,3,3,4,4,4,4,5,5,5,5,0];$Gc=[1,2,3,4,5,7,9,13,17,25,33,49,65,97,129,193,257,385,513,769,1025,1537,2049,3073,4097,6145,8193,12289,16385,24577];$Ic=[0,0,0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13];$J="";$G=0;do{$Rd=inflate_bits($Wa,$G,1);$U=inflate_bits($Wa,$G,2);if(!$U){$G=($G+7)&~7;$v=inflate_bits($Wa,$G,16);$G+=16;$J
.=substr($Wa,$G>>3,$v);$G+=$v<<3;}else{if($U==1){$hg=array_merge(array_fill(0,144,8),array_fill(0,112,9),array_fill(0,24,7),array_fill(0,8,8));$Jc=array_fill(0,30,5);}else{$gg=inflate_bits($Wa,$G,5)+257;$Hc=inflate_bits($Wa,$G,5)+1;$D=[16,17,18,0,8,7,9,6,10,5,11,4,12,3,13,2,14,1,15];$Gg=array_fill(0,19,0);$Fg=inflate_bits($Wa,$G,4)+4;for($q=0;$q<$Fg;$q++)$Gg[$D[$q]]=inflate_bits($Wa,$G,3);$Hg=inflate_table($Gg);$Xf=[];while(count($Xf)<$gg+$Hc){$Hk=inflate_symbol($Wa,$G,$Hg);if($Hk==16)$Xf=array_merge($Xf,array_fill(0,inflate_bits($Wa,$G,2)+3,end($Xf)));elseif($Hk==17)$Xf=array_merge($Xf,array_fill(0,inflate_bits($Wa,$G,3)+3,0));elseif($Hk==18)$Xf=array_merge($Xf,array_fill(0,inflate_bits($Wa,$G,7)+11,0));else$Xf[]=$Hk;}$hg=array_slice($Xf,0,$gg);$Jc=array_slice($Xf,$gg);}$ig=inflate_table($hg);$Lc=inflate_table($Jc);while(($Hk=inflate_symbol($Wa,$G,$ig))!=256){if($Hk<256)$J
.=chr($Hk);else{$v=$Vf[$Hk-257]+inflate_bits($Wa,$G,$Wf[$Hk-257]);$Kc=inflate_symbol($Wa,$G,$Lc);$nh=strlen($J)-$Gc[$Kc]-inflate_bits($Wa,$G,$Ic[$Kc]);for($q=0;$q<$v;$q++)$J
.=$J[$nh+$q];}}}}while(!$Rd);return$J;}function
inflate_bits($Wa,&$G,$Yb){$J=0;for($q=0;$q<$Yb;$q++){$J+=((ord($Wa[$G>>3])>>($G&7))&1)<<$q;$G++;}return$J;}function
inflate_table(array$Xf){$Q=[];$xb=0;for($Xa=1;$Xa<=max($Xf);$Xa++){foreach($Xf
as$Hk=>$v){if($v==$Xa){$Q[$Xa][$xb]=$Hk;$xb++;}}$xb<<=1;}return$Q;}function
inflate_symbol($Wa,&$G,array$Q){$xb=0;$Xa=0;do{$xb=($xb<<1)+inflate_bits($Wa,$G,1);$Xa++;}while(!isset($Q[$Xa][$xb]));return$Q[$Xa][$xb];}if(isset($_GET["file"]))load_compiled_file($_GET["file"]);function
load_compiled_file($n){if($n==""){http_response_code(404);exit;}if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){http_response_code(304);exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");ini_set("zlib.output_compression","1");$zd=pathinfo($n,PATHINFO_EXTENSION);switch($zd){case"css":header("Content-Type: text/css; charset=utf-8");break;case"js":header("Content-Type: text/javascript; charset=utf-8");break;case"ico":header("Content-Type: image/x-icon");break;case"png":header("Content-Type: image/png");break;case"svg":header("Content-Type: image/svg+xml");break;}switch($n){case'favicon-blue-0f5ce53a66b1e25395d0048da369f19e__aff407a3.ico':$f='AAABAAEAICAAAAEAIAC6AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYFJREFUeNrV1wEEGmEYh/FztCYBRATANhCAAEGAEGZowEUFhM2G6A4QAJksoMi2AYRlAxgcAUgthAS2yTFo5d2DDzbO6r2PhB9APY73z+cUn3+6qbsJcFGCjxlCbPHL2CLEDD5KcG0EPESAH5ArfUeAtDbgCb5BElrjsSbgI8SSD5qAM8SSsyZAbNIErCGWrDQBTYglTe0ZNnCAKB3gJR2iAnwsIBdawEchyRC9jompoYUe3hg9tFCL+dNX2ivo4wEcpTT6EF0AsEMHeTgXyqODnf4M489phC7aeGq00cUIK1s7sLr1DryEWPJCE5DBBJLQBJkkO9DAHnKlPbwkO/AMjuGijCGWiCD/iLDEEGW4f/2WIuA3qnBiZPHIyMKJUcVJe4ZHDJCDc6UcBjhqz/AEMSKMUf9PTA51jBFBAN0X+AKJEWGDr8YGESTGZ02AB7HE0wSk8B6S0DuktDvgYgRRegvXxsuogjnkQnNUrL8NUUSAKUL8NEJMEaB4x4/TG/gDMBOIUjRp9w0AAAAASUVORK5CYII=';break;case'favicon-green-def78cfa7c465c8b0e9966e3eb87407d__aff407a3.ico':$f='AAABAAEAICAAAAEAIAC+AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYVJREFUeNpiYOhiAFBfBxBohGEcxs/RmgQQEQDbQAACBAFCmBDgogLCZkN0BwiATBZQZNsAgmwAgyMAuRZCAtvkGLTy7sEHcFbvfWT4AbjH8f75Huq/CXBRgY8lQuzx29gjxBI+KnBtBDxFgJ+QO/1AgKw24AW+Q1La4rkm4DPEkk+agCvEkqsmQKxSBGwhlkSagA7Eko72DNs4QZRO8NIOUQk+1pAbreGjlGaI3ibENNDFEO+MIbpoJHz0jfYKRngCRymLEUQXABzQRxHOjYro4wBJGwAAEaYYoIeXRg8DTBHZ2oHo0TvwGmLJK01ADnNISnPkFAEA2jhC7nSEl2YHmnAMF1VMsEEMAQDE2GCCKlw4RlMT8Ad1OAnyeGbk4SSo46I9wzPGKMC5UwFjnLVneIEYMWZo/SOmgBZmiCGA7g98hSSIscM3Y4cYkuCLJsCDWOJpAjL4CEnpAzLaHXAxhSi9h2vjZVTDCnKjFWqw/jYsI8ACIX4ZIRYIUMbfEW3maO8YAIxSqCXQN5/tAAAAAElFTkSuQmCC';break;case'favicon-orange-cd68622e75276fdf7c60d1e9d4deee14__aff407a3.ico':$f='AAABAAEAICAAAAEAIAC7AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYJJREFUeNrV1wHkGnEUwPFztCYBRATANhCAAEGAEGbIwEUFhM2G6A4QAJksoMi2AQTZAAZHAEsthAS2yTFo5f2/OHCcv3v3I+EDcO/reI+f9fO1dVN3E2CjAhcL+NjjX2gPHwu4qMA2EfAUHv5AEvoND1ltwAv8gqS0xXNNwFeIIV80AVeIIVdNgBilCNhCDNloAtoQQ9raNWzhBFE6wUl7iEpwsUoweAUXpTSH6H1MTAMdDPAhNEAHjZih77RbMMQTWEpZDCG6AOCAHooJBhfRw0G/hvHrNEEfXbwMddHHBBtTd2Bz6zvwFmLIG01ADjNISjPk0tyBFo6QhI5w0tyBV7BCNqoYY40AEhFgjTGqsCPfShzwH3VYMfJ4FsrDilHHRbuGZ4xQgJVQASOctWt4ifzeKZooPDK0iSkCCKD7A98hMQLs8CO0iwyM+qYJcCCGOJqADD5DUvqEjPYO2JhAlD7CNvEyqmGZYPASNRh/G5bhYQ4ff0M+5vBQvrfH6W09ALYCTFLvUfsXAAAAAElFTkSuQmCC';break;case'favicon-purple-d4b02fdcc3abcc374a77c65f88513c01__aff407a3.ico':$f='AAABAAEAICAAAAEAIAC6AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYFJREFUeNrV1wEEGmEYh/FztCYBRATANhCAAEGAEGYIcFEBYbMhugM0AJksoMi2AYRlAxgcAUgthAS2yTFo5d2DDzbO6r2PhB9APY73z+e8Ln66qbsJcFGCjxlCbPHL2CLEDD5KcG0EPESAH5ArfUeAtDbgCb5BElrjsSbgI8SSD5qAM8SSsyZAbNIErCGWrDQBTYglTe0ZNnCAKB3gJR2iAnwsIBdawEchyRC9iompoYUe3hg9tFCL+dOX2ivo4wEcpTT6EF0AsEMHeTgXyqODnf4M489phC7aeGq00cUIK1s7sLr1DryAWPJcE5DBBJLQBJkkO9DAHnKlPbwkO/AMjuGijCGWiCD/iLDEEGW4f/2WIuA3qnBiZPHIyMKJUcVJe4ZHDJCDc6UcBjhqz/AEMSKMUf9PTA51jBFBAN0X+AKJEWGDr8YGESTGZ02AB7HE0wSk8B6S0DuktDvgYgRRegvXxsuogjnkQnNUrL8NUUSAKUL8NEJMEaB4x4/TG/gD0xZAYUYkFLAAAAAASUVORK5CYII=';break;case'favicon-red-c2ebb34a8df5aba28e15d87728a151df__aff407a3.ico':$f='AAABAAEAICAAAAEAIAC7AQAAFgAAAIlQTkcNChoKAAAADUlIRFIAAAAgAAAAIAgGAAAAc3p69AAAAYJJREFUeNrV1wHkGnEUwPFztCYBRAQY20AAAgQBQpghwEUFhM2G6A4QAJksoMi2AQTZBhgcAUgthAS2yTFo5f2/OHCcv3v3I+EDcO/reI+f9eOZdVN3E2CjAhcL+NjjX2gPHwu4qMA2EfAUHv5AEvoND1ltwEv8gqS0xQtNwFeIIV80AVeIIVdNgBilCNhCDNloAtoQQ9raNWzhBFE6wUl7iEpwsUoweAUXpTSH6H1MTAMdDPAhNEAHjZih77RbMMQTWEpZDCG6AOCAHooJBhfRw0G/hvHrNEEfXbwKddHHBBtTd2Bz6zvwFmLIG01ADjNISjPk0tyBFo6QhI5w0tyB17BCNqoYY40AEhFgjTGqsCPfShzwH3VYMfJ4HsrDilHHRbuGZ4xQgJVQASOctWt4ifzeKZooPDK0iSkCCKD7A98hMQLs8DO0iwyM+qYJcCCGOJqADD5DUvqEjPYO2JhAlD7CNvEyqmGZYPASNRh/G5bhYQ4ff0M+5vBQvrfH6W09AE8YAEN5XivhAAAAAElFTkSuQmCC';break;case'favicon-blue-17e440832c1eac07527560a0d6f0d2ee__aff407a3.svg':$f='+<bATb3V?$so%el,wEIwK&mlYjGZ$a8-HGs8y$j)-UBmQx`Cf?>]C6?xmhS1<w
ZNSJl63"aZ]nB<rgk|tG)vC,pHYx;Wb2NVXBMxd!lQ=g0"!mM[]c*SW?7
E^]B7t[fHolNczfsymCW
CM~8Ult[(brlOx`71nDc
H;N~ybgNd*l6LMbRk;>%06rQeBnDc14r_7Z0b9uqO=xV5=22d05hr#V]F`V>ZD,#JA9[$XEV-*TBfz7Z%
rEJw!G!cT+[7>-u:iVU)N$iv%ySN.`._&}sV@FUIuH)%cY-0#OXWPT8t./3Oid8~R]3?9n;/Qs
Y2!K`1Q9d
tys6C=xmJfXFCT%0xl`H&%njK7`N[.q6jET6kV
VqmiJoIrZ#rsP~q0vDN<FH1w9l4R-?A#H:#onn0@0]3dNk3,E{<7r;2q
u)F!d(nhskXC~JT4N!~!g52r#`3hF[%j
oPE~ZV(W_~g#t?WXQCxEe7)ZQKGxei4.gu_R>pAL]HDZf!uSL)$_)^vZ:Xk![_HKh=C|S~PjqHcgjUutq#-~?PmA#<MYyg2R';break;case'favicon-green-bb254c95a033f67e3d433a3df63e160d__aff407a3.svg':$f='&<bATb3V?$so%eo_t_hLePj9EjS(Ic^3}i[Os!U@i%X`9w9h7
[@
I:KPV|A4v9o?tG^4G8Kl/Za|j!Q;%pO#+U]tOQ@f!m@5m(iU+Xd
:mOOX[.7/V%^=Dj0`KWYnDy%^,cfeZsrK*5P&I[0
$m~<+JorKkYLM7yy$Aw$[w/57.-wX^0JIMH;`
EW/c}a9c

eAhG[68u92FB)
ToD/k0uYFG=PN>>d,^BJ&icL,/i,2N?:udHuPgxk>l07BF^82,>cspK:`^P,q[%?5=4<`h.?.kJ"X_a_mu|Z>-SL,/k)gDk#g):;if"T:5u9
?>JfFYF#KP)D%8xDljc2l^mJ<^%4+Iu=v?FB.Y^67X>9:@KMfCyqQL.-Kmn#DsAler%=^*ps8vI)CEG%8
bknN$znn
D-t3DO&3,C5<7r@2q],)F!d(nhsf)H-JU7?!^"9+Xtpm.q8>NCVHgU$kcVodiJa6[qLov!)IR#}x3*ku*P%3jGe?w?dILBVvhJ~,bFMpBcD%CK/#{0d-|Mu[Pn{fe.3aFbOf6.%,.opHOIuO9';break;case'favicon-orange-53ca3b502d7fb29f01bfbf87fc4d6b24__aff407a3.svg':$f='&<bATb3V?$so%el,wEIwL!M$a_R(Ic^2cYP0
-+_=VtkTnq5G
]9Lh@D6K6GH(sVGneM;s+raV;M^D!*+KY*IOa=3Z`PmB;9_2Obq7yBg2Z%>bw,v+Y%P7|fhq*F156s0beWd,6`ay=p@_gN3Z=jciDmX_i72]oyww{Z~&Ut4M]*axgl)b!a1ju/uH<*9Xo
=
Ul9*$m_7{*MB>d@6aDhpC0Rt_1D-,/u//bLNxN!Dc[m?bh@-`9rmyGW2b(F%J[Qafv1UZr>a>F6JA8g[!0b@[fJtmTv4e%TYc-0#j"5iK2z=wl
*E@MT<:>g<H|W|2(rwB
g5j1,D7hGqj!`uuvXM9_hJa7+NN~VgB]NK[T3a!z#1A}OTmcvh["nTvNeTw$$;eT6q?q1?CDM!=<1SnN]QTt9lj&eNk(4c>}6hisx;^`kO(cefsRGicdC|"j9npLdO:cM^+&QT[{IL-Wu]3@7yXoO46wh/&9Sd+Vo~(7pzhcG()N3^n#?6MAX=Pd
]uX2I0XtRaah;-u5eKzydbLJw$UJhJ]
fe:Fe6mt>cu';break;case'favicon-purple-4cfd57d31ab991e8071fe34060cd3123__aff407a3.svg':$f='+<bAU6+V?$so%eoa6[DcHOAP_Cx(^^F;2nJOs!U09RM?Tw1h7?fDMh@D6K6GH(sW:c#bW`9DTcKpCf,1a1E%kD+5q+F]4B,8UB|ML]o,4O$F:-Sutmd.1l:6ly,?7MjE>rM=td#VYm/o}bPN3Z=AH2GFCrK4TIrcvu65o!8H3d"9V^}B7s3mnUM&Wx87ya:7X
mAh1Y7Wu72FB8FRoD/k0uYFG=PV;h%?KNUP%#GQ^
2w"
SkNgvI0*kTGqx))i0|m-597vz$R_y_,e<pwj=Y`"7[1E)$2(fp:Ex.$(:Op<o1CeTD_yBV
#FT:r
y-y?.@i`K.+Yg?j_Hk`_>f$l~LS;5RSh?>{b"
&+evz;:0eol+,pv/]hQo|q*A;p{F69^H{
F#X:3c%4:=Hdpu{k&lMXV@E/#GL#?#)e-WHQiGP@.%eYsSRXBg!n9tO*z&WP*$Up~J1]E18NCC{!iER:u`UBQ5(*#viS-0S(llO)OsT0bALBjGk4Bt}3Hwkvp!81Fs-^5[}2H/0[(gXX++<t3ZZ577
3fS]`cB;^5uWM3tX';break;case'favicon-red-a006e401273230fd6be80568c8361b57__aff407a3.svg':$f='+<bAU6+V?$so%eoa6[DcEe<SKeo.[BnWu^_0
-+j@96@+X_5GA4^m3%R;yn_USCF5vXi6B6jvyvvy?qZYfND@5~KR9wPw1q,+w{:cwGa2aY)<GPqWy/nLYzy>c3Au_/MA7,dc
}`rf-`x<FH$&bI]FGspJPra.)yE]$w~aKaM]on_y4%i2=`y?0`vYw6}rUy&B3JD1F
/B
o8ro30<1Tp4r,.qWnFpndQQq#ek`C+.f19/9#q+uNg4bc6H.9(02NJtu){yYINu`Uzs:%?o1GH"Lgtxvhu>9uq8)/:th8&TH,OT*]5<Ydlap!w8k>zUUDvh@h+)F+>T8=R`(;8*p2Il^<$eSAzo6lU8L_P_Yh-i#lD(4lV7"52"_UdLUTuCV+Yf/[^)f(~i
.,!Hkau}dsr
i84
>s)_:!#hJQ9:ex&|].#nB#/g7`Ds1xz$U+"f!p-.*YXigq8vUBF!9lVoojveL
+8ox,X;hOaXS*cTW+ODnn.]r,!3BBNvdJ~,brQumcAQJa9E)x*rt!$x~u(xCb
69OF`xI}1->oD?M:yg2R';break;case'apple-touch-icon-blue-f2a5f6f50418d7293b806faf273fe381__aff407a3.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAAK7UlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUa502RxeG0bX17btm3b/tu2bdu2PbZt21bY+6xqPduTSSddSd1zPyTdVZk+p5+prr51f7d83ixVOXXH55Yte6No2r25Qy/O/Pg7OB/4ykFO0cC/4FBma6qq2Tsmb+SVGe9/7f86zWhMFx+HQ5mjs7XmwITMT77LXe+R04WOdFdw+Ka1xOzL7vML7rTLTnd+RMHhW+Z01Owfz911izOE8IMKDl8wh6W9dPHLbsFCOD/Izyo4pB8z3EyG4GPJK5rTqeCQ2HgEGECGeL5MVHBIPAM1CAvh/AkFh5Rvrf/13STr0x/UHpmO4yXzn+7+3pev+VA0puN/fX/hDyk4fOSBkjPg96KN09KRN/KqbuBoSzsnGtNRz8NFwSFBDJRAeDdwCOsqS8/86Nu9gYP4mK25WsGBaXVNlpS8plORFeuP5k/ZkPbVnLj3J0e9Nib8uWEhj/QPvPvz8zd/eAa/54vzfOUgp2hAs6kb0uhCR7rXN1s0I42AN7dNDxxYY/CG3sCB1+wb66dwNLVaQ5Jqlu/L/XxWLPf+hvdOu8X5qS9mxS7fnxuaVNvcZtXcaiyI6IeDN9KyFW/3Bg6eTf4FR1p+0/ydWS+ODL3xfe6lsc6feGlk6IKdWWkFzW5Za+WG6YNDzF5bWIx1GQ7cUpXr+3DklLQs3pP9zNBg7plX/NmhIUv25OSWuv4KwFK7TjhsjZXic2dRQsYH3/h3OFLP6oSDP+rLcDAV6DM3jttjEu83P961gYRUDJ1wVGz8wtZYIb7Wn136b41bk0/phKNs+Zu+CUd6QbPAwmzeb158ZlHPECFVRy8cGz6nsdNuE5OP0kUv/gscCUd0wlE07T5fg4PFgQ3HCni5MCEWwrm8zScKNd3G7EEnHDX7xnGkes9occTe3pgz8I//HgDVAUfu0Et8Cg5eQxi6xT0wuQ9amNDSbtN0GBEOnXDUn1nytxEi6aQ42JEbIRo3hW/XCQfRDt+Bw2JzvDk+QgoshL89MdJqc7gRjqawrRzBs774ibWuWByvOzabg3hj4Fp/hGPaxnSJsBA+a2uGGx8rLXGHxPHCSXc47Vax0F8853EO1p2c73ePFeJO0mEhPDKtzl0T0raUM+I4XrVjiDhlb67J7vdrQp9+NyGdKuGwIXzWlgx3vcoS4/q3s4wl4mx7RkDlxi/97lWWuKe8cLw6JtzFIJgOOLI++6G1pkA06CyI9bsgmKRYiDdbF8PnOuDAC8bf7LRZRBuZwucKjps+OKNn4c1lOPDKLf1EG19beFNwsGTfGzjw5ug9oplPLdkrOEj2IfDQGzgyP/2+EMr6UrKPgkOkCboOB54/5jqntVP+NEEFh0sJxuQPc6QbL5n/jN8lGN/+8Vl54bjrs3NKmmCgfTg1Wl44Pp0Zo0RNBtri3dnywkE2q5JDGmikz4gcDrn81o/OksjodSE1ZPiykHrt4XwZ4dh4vMA1OTXPF3c+TcSY4ZNwOJzOT6bHyEUG2ginUxVv8Yi1d9oHL06UhYzhS5M6uuzuKPs00dWyTxP9q+wT/4hrDuUhGzF1YOP9M0jl3CuWJODdvUpWOM1oTAzUTwvGRafXvzHOpCmDb02IiM2oN6zUZC5L7aRikKpDKhfDA84HvnKQUzTQVKlJhpDTkZXPDQ8xDxYvjAg9G13ldKoiteYwu915LKyceR8juRcfImhoj4dXOBym5EKVmqxu6Nx0vACBvCexeH1c+JYThTUNXZoyKVT25bUdjCWUWkDobIh+elQYpRmOh5VX1HZoyswAxwdTo4orexzga2yxBCXU8M89fVP6l3Niqb1xS0/CrDSmC4U66M6PBCfUNLZatR5aQXnbR9OiFRzGLtkz9+x9VQymBRV1ncSzk3MbUQwExFefCK/YF1C6P6CUD3yNSqvjFA1o1vs5BHQ+PSRYLNkbZQoO/LOZMUJAZn5Dovfx9GiRz2GsKTjwdydFVtV3aqY3Bp63J0RwwQoOj2aCPdQ3ICK1TjOxhSXXPtAngEtVcHgnTZBFuIiUOhNiwfRTXKSCwxPWTaz6XEyVw9tRSS7gTFSliOgrOEyUYIxY8nBImc3uBUT4oweDSp8fLmIqCg5TZp8/3C9gxPKk3eeKqd1m6FDCj/Ouu+tc8fBlSUyAxAUoOOSQJjzw1QUK62w9WUgFN8IVblnESc1ropjTwIUJ9391wQBpgjdM6Vbu/uw8AZIxK5PnbMskF4ShheVcgl1ZRS2VdZ2dlr/l4/CBrySrEhyjAc1oPHtrJh3pjrzAAN2K6U2Jmm7/5JyhuhgFh7HG/ZNXmsDQpeAw0N6bHCUvHGrhzVibuTlDXjjmbs9UcBho7GshLxyE6RQcxoYgqekpIxls3UJcRMFhuCKSab901cCEFlLBYawt25cjFxwrD+SqNEHPGYp1WchYdTBP5ZB62rafLjK/4o0Aq+YVU9nnF+KqHxsQZE4yHh8YRFazhik4vCiqZkc3UxXtIFt90a5sIZ5WcHjZ8spa2bLJ608ZLqDvvLj8slbNbKZETUha5m3PvO/LC57HguV7BrDiqnZNmbfg0JNR3GWxkw+Gbva2jwwvPcgq7pezY4+ElHdZHfovXsFhiDH/Z2s3nY2hhNxj/qHdK53l2fH62PCFu7K52TChv1oVsCo4DLS/ZkUkZjdqPbSGFktsZv2+CyWsfjEtQH+mU49PM/aqpQsPLCRx/AjyNa2HRi8uG9eMNpXs88SgoNqmrt6nBCN5RW9NYDspt5ExhoUxnA985SCnaND7dGXU948OCPRcso/KBHtqcHBhRZtmessvb3tyUJDKBPOE/dsLgni+mNPisxrEq5OCw3D7z9VOahoz8dRMZmQpM2Pl8rycQ6oSjJkwMlEwlRaSp55KMPa0dZ+hyVqG0+nN0nUU9vhgSpTKPjepNIEiT7xwincZz1htY9feCyUUEVTSBDl0K+zTSfhLxKncbkx0wlNq5+3IemV0mCl0KwoOBGcuSJWoA0YtL7ck6mWXtKCFpKSkC/In3lw005rSrTzYJ+DN8RGUfhu7KoVxhZ0MWIUJSapJy2+iKCBjDOMBH/gaklhzKLiMBjSjMV0orEB38+pWFByUjpRXmjBjc4aCw0Cj3oG8cDBTVnAYaCx6oTiVkYx7vjjf1GpVcBhrpPnLCAdL9irZx3Br67SJEn2yOMWGPJRSqtIEqb4lFxykiqk0Qc8Zy1qykLF0b44mv0m2jdeoFcnmJ2Pc6hTN86ayzyl/TnKvmclALUEimYLDaxs0kdppTjJ48Nm9TobSrZD4effn500V0kCnqQlTcHhdzmSSbSLZI6Gk2jTSJgWHKPpzNLT8SZGC5XFH7sDufyLbSMFhxp1vtp4qonCxJ7EgKEc9CDn2B1IbALa220jgQJdmNBY8y2CRoK0miyk4hFEbn2oIbq/hgapq8Z7sPKnV9AoOMR1BkMjSF9XsH+kX6BoQj/QPpMb+uiP5qFF0TCwUHHIade/ZTnzJnpyJ61IHL0pgdz7yQFEskvlHTiEf+MpBTtGA4DevylLsJ/endulABgAAAGCQv/U9vmJIDuRADuRADpADOZADOZADOZADOZDjBTmQAzmQAzmQAzmQAzmQA+RADuRADuRADuRADuRADpADOZADOZADOZADOZADOSAe34f5izVe/wAAAABJRU5ErkJggg==';break;case'apple-touch-icon-green-903cc109ea077cd9e91508416c5e335a__aff407a3.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAALGklEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUc7syxR/Nq2bdu2bdu27fvZtm3b9nds2z5J+v2e+vGeOyeZSbqT2qv+GHRPZq3ZaVTXro56NBYklUz5LafzQxnfX5783rHxz+6EccApF7lFgdgih6C5oqBo1KcpH50c9+Q2f2oUozBVopwcAn99ddHYL+Of25mv3iqjChWpLuSITlStGp346n586aCN6jxEyBFdCPiLxnzB13XFaEJ4oJAjGuBvrM3ucC8f1UXjgTzWenJIm+EyMzQ/Ot6nAgGLySGgC/CAGbp/+cpicsgIlE/oqfETVpJDZq3/d26S8PxuxRN/+Ltltbm15W+f2/NpXZiK/3f+wg8JOaKkQ0l681BdJtBYl/LRKS2Qo2bLbF2Yik46FwvIIT5QHOF/Sg7QkLM1/pkdQyEH/rHmykIhB1AFNQUr8lYMjx/+0/KfXpzx4s2jb750yKVn9T/rhN4nHNr10L067LVDmx2wvTvuzSkXuUUBir008yWqUJHqhbWFykvg8OazOSEHKF/YNxRyYEWjP4tRcpTWl05NnfrF4i+uH3k9336b37ZxxXjUDSNv+HLxl9PSppXVlylXwYKIc3IwI83p+mgo5KBvii1yrMpf9f7890/pc8q2v23Lt/TU+IlT+576wfwPVuevdmWtlQ/mjBx69FrFYmzQ5MAaC5KjnxybijZ9svCT43odxzeLiB3f6/hPF326uXizChYstTskR3N5vj6uz1gX99R2/02OzbMckoMfjWZyMBS4ZfQtfB5D7PYxtwfXkBCK4ZAcef1eai7P06elszr9V+HqjdMdkiOny8PRSY41BWs0LUyz28bctq5wnWoNCNVxSo6+L1I44GvWg4/s9nf/BznWTXRIjozvr4g2cgRU4JcVvzC54DMYa7ze76t+V47B6MEhOYpGf86VwpGf6Cu+2vKktw7/LweoE3Ikv3dcVJGDaQhNt/4Ghtvd4+4ubyhXDoCHwyE5Smd2/EcLsWGavliXvEwXrlg6xCE58HZEDzkafA3nDTzPClpou3DQhY2+RhfJUbFk0D986i/t1VSSqa+XTP7l79fL5/eKRXK8MusVi2ih7c05b7rYrVStGa+vp399UcDXpBf6M3+9kYsl09rEXLeC38k6WmibnTHbrQFpzaaZXNFWMPRdfctXWZT4+oG4PmNuQPryzJftJccbc95wayqLj+u/7tKW6Lu1cfPy+70cc1NZ/J72kuPM/mc6cIIFSY6EF3ZvKkrTBerTVsecE8xSWuiZrQP3eZDkwNK+ODfQ3KjL2OQ+F3Js//v2ThbegiYHlj/wdV0m2hbehBws2YdCDqxy5UhdLKqW7IUcBPvgeAiFHPHP76qFstEU7CPk0GGCwZMDS/30jEBTvdVhgkKO4AOMiR9uOcA4q81tMRdgvEu7Xewlxx4d9hBpgoe4YtgV9pLj2hHXiqjJQ3y88GN7yUE0q8ghPQThMzqGwy7bqe1OBDJGXEgNM6JZSP3D8h9sJMevK38NTk5N/+Jmb6LbjKgkhz/gv3r41XYxA20EoWuSvCUcqG6svnf8vbYw48GJD9Y01biR9umrYNM+fRVbaZ/4I3637DutTDHTtvt9O6Ry7oolcXi3rJLVRjEK4wON0YRxczPnnjvgXDOZcf7A8+dnzfcs1WQyS+2EYhCqQygXzQPGAadc5BYFlKSapAkZET/ixN4nmkOLk/ucPDphNC8mSWqNQLO/edCWQYz7aMkj2ImgoR2ydYgv4FMGQlJN5lTn/LbyNwTy4aTFOQPOabOqTW51rhJYobJPr0inLSHVAkJnL/TTp/U9jdQMg7cOzqjMUCZAyHH50MuTylqd1bu4rnhSyiT+3K/OevWmUTeRe2PHNjs6pwKFqUKiDqrzkMkpk0vqSlQrEV8af+WwK4Uc3i7ZM/YMPSsGw4LMykz82ctzl6MYmJA8YWjc0J4bevba2IsDTudkzOEWBSgW+hgCdh7b81i9ZO8VhBzYdSOu0wIy84FE76phV/HaQo4wBftcPPji7KpsZTxoeC4YdAEvLOQIayTYQV0OmpU+SxmM6WnTD+h8AK8q5IhMmCCLcDPTZxpIC4af+iWFHOFAC77qMYljWLBVEQUvMDJhpPboCzkMCjBGLNl/c/8mf5MKO/jRPpv6nNT7JP0yQg4To88P7nrww5Me7rquK7nbPF3g4OHMdbus6/LQxIcYAOkXEHLYIU3Yv/P+JNZpu7otGdxwV7iyiLMybyXJnO4ad9d+nfbzQpoQAYhuZc8Oe+IgeXzK42/PfZtYEJoWlnNxdq0vXJ9VlVXb9I/ISg44JVgV5xgFKEbht+a8RUWqIy/wQLdiPETUtGu7XT3TxQg5vAffz15pAk2XkMNDXDLkEnvJIQtv3uL12a/bS4535r4j5PAQ7GthLzlw0wk5vHVBktPTRmawdQt+ESGH54pIhv3WZQPTWkghh7f4fNHndpHj6yVfS5hg+IBi3RZmfLP0G4khDTc6rOlgvuINB6uKCCT6fHzS+MO7HW4mM47ofgRRzQoIOSIoqmZHN6OSdhCt/tGCj7R4WsgRYWwp3sKWTRHvZXiBW0ffurVkqzINImpC0vLuvHf37bRv+GnB8j0NWHJZ5OTLQg4nEcV1zXXEg6Gb3bntzl5zglXcG0fdOGDzgPrmekcv7x2EHIz/2drNYWFYQuwxf2iks+72HWcPOPvDBR/yseGE82xVkFXI4SH+HhWxJGeJaiWKaosWZC3osaEHq18MC9CfOdTjU4y9aqlCh4UkjocgX1OtBLV4bUx5DQn2ObL7kfk1+aGHBCN5RW+NY3tZ7jLaGBbGMA445SK3KBB6uDLq+8O6HRa+YB+JBDum5zEJpQnKeMSVxB3V4yiJBAsH/muCoPsXM7Eoe5GeOgk5PMf/rnaS05iBpzIMRCkzYuX1JIY0wgHGDBgZKBilhaTXkwDjcKPlCE3WMiIYUMNPk9jjsqGXGRp9LtIEkjwx4dRzmfAgryav+/ruJBEUaYIduhX26cT9pf1UroOBzoy0Ge/Ne++MfmeIbsUIciA4C0KqRB4wcnm5Eqi3sWgjWkhSSgYhf2LmooyF6FYO7HLgeQPPI/Xbk1OfpF1hJwNWYaamTl2Vv4qkgLQxtAcccDoldUq/Tf0oQDEKU4XEClQX3Yq55CB1pL3ShNdmvybk8BDkO7CXHIyUhRwegkUvFKc2MmPvjnuX1pcKObwFYf42koMlewn28RxVjVU6RZ8tRrKhMIWUSpgg2bfsIgehYhImGD6wrGULMz5b9JmyDPZv4/Xo5EfNZ8ZTU59S4YdEn5P+nOBek5mBWoJAMiFHxDZoIrTTTGbQ8fF6KrIQ3QqBn3t12MsolwY6TaUh5Ii4nMmQbSLZIyGlPEUZAiGHTvozcMvAo3scHSlaIHdg9z8dbSTkMHHnm3ar25G4OJy0wClHPgg79geSDQArGioI4ECX5jUt6MvgIk5bJbCFHBrkxicbgus5PFBVfbLwE6T9SmAvOfRwBEEiS19ksz+k6yHBEeLQroeSY//H5T+iRnE+sBByWAby3rOd+KeLPn1u+nP3jL+H3fmIA0WxSOQfMYUccMpFblEA5zdTZSv2k/tLu3QgAwAAADDI3/oeXzEkB3IgB3IgB8iBHMiBHMiBHMiBHMjxghzIgRzIgRzIgRzIgRzIAXIgB3IgB3IgB3IgB3IgB8iBHMiBHMiBHMiBHMiBHBDxnn9eIYwbtwAAAABJRU5ErkJggg==';break;case'apple-touch-icon-orange-6efda14fd1d3c45382c67d7f324bdccf__aff407a3.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAMAAAAKE/YAAAAC1lBMVEX//////v7yyqv//v3qrX399/PZaRL44tLjkFH9+fX007rpp3XWXgLXYQbbciH34M7jkVLggzvkk1byyqz007nYZg7+/Pr228b++vfqrHzhiEP33svii0j67OH67eL++fb//fzwxaTopXH+/PvstInwwp/++/njj0/XYAXabhv559rZaBHhiUXhiETbcyLii0npqXjZahXghT/YZQz228f66+D23crXYQfijEr45NX45NTz0LX89e/ffzXZaBLstYvhh0Lww6DpqHb9+PTabBfefTPjj07opHD12MLxyqv22sTbbx378OfbcSDcdSX89O7aaxb67ePz0bbnnmfpqnnjkFDmm2Lll1vbbxzklFbffzb11r/abBjkllnghkHkk1X23cntt43338ztupL44tHijEnqrH3oom355tjlmV7z0LbopG/rsYXvwZ3fgDfccyPfgjvuvpjoo2700rjopXLzz7TvwZ777uTstYr45dbbcB788er11r7ZaxX66t7YZAvww6HbcR/tuZH00rnrroDcdSbnomz01b3eezDwxaPstoz34c/zz7PdeCr55tfxyKn56dzzzrHnn2jfgjrnoGrpp3Txyan77+fklVn99fD88uruvZfmnmb77+bqq3vefjT++/jZaRPtt47vwJzefTLdei301Lzvv5rxxqXyzbDnoWvfgTjnoGnhiUb77uXttozeey/qrn/99/LZahTtuJDuu5TpqXflmV/ijUvlmF389O3mnWXvvpnddynwxKLssobxx6f88+z669/YZw/23MjhikfghD7deSzabhrfgTnyzK/YZQ388uv118DzzrLxyKjrsILuvJX449Pdei7cdCTghD3z0bfbciLggzzklFfss4jWXgHXYwnXYgj78enefDH56NrstIr56Nvqqnncdif78OjYYwropnPqq3rXYgfcdijqrX7XYAT01LvrsYTWXQDabRnWXwPcHI28AAAFZElEQVR42uzBgQAAAACAoP2pF6kCAAAAAACYHXuAjqRLwzj+1MS27Yw939i2Z23btm2PbduKbdvdwZMce42u2uqkC3NuLX5Hcf6Nqnve9z/e/3V0hoYEBgQEhoR2duA/Qm93Dx30dPfC6jzcbVSwuXvA0iZ5UoXnJFiXWwydcHeDRfl40ylvH1iSmzfHESzBitw5rkFLXoOcgAWvRg9P/lXfyMjIu/lPd//8aR//ytPDsm+OUQBJdv7DMIBRq75BegMcorEoWy3a1vbK/nvOs6cPcwura0YjhoZejtZUF+YeevosZ6L/103HaHxYLZoDMN+DJTGxEXQiYvp7du+AUz3yaOl3atF2mGxBv72ZE2h+/Os4qOqgPBo3AlWi2QUTec2aQRd9dG8V/l2nLPq3AIL8+Re+suhOmCYnnprci4NSqCz60GEA7+Rf1MmiG2GSd8RTs4VBkAuRRT8MSQOkLJL8uSy6AaaQmoaow9AKyATKoo9wFoClk0lOkkXXwwwP7lGnrKVwECCL/gb5AQBRJFNk0TaY4Ngp6rYs02n0EzIiAUATedb86F/QgPc6fXsUkXxDJOA2jT8w/e2xm4YMO7sQv0mSqQC++OUB0y/EPTTkO85uecP8iyIAX9pj+i3PTkNOKw4XRfRXvwIgzvTDhcYMKY5xRTTbMwGYfozTmDH8S49KNC8oou2wVnS3WjR/I48esFh0r00tuqUDzocA8dFwV4tmfrT6uCU0Wn2wvct/Wmj6YNtKQ+YKWSE00JC3CFnW3KEhMULWYkFDNCDAS8MCMtgHZhmhAelQcnOnE4MSTOM2h7rFSqKW6h7e1Omta6DGY9BGBdugB8wlVTRTB/+ncKZ3wE4H9oE2mO+77dRs3Rsxnq7OxoZ6m62+obGzC6+GVL6dmvRskiBe2pNYf7rIf3pKMizis5+rpgu+d/UKLOXFk4eP6Vzzjw99fz7E+9gUKJSUXb10uSabMtk1uZeu/ug5FH5SCxG4fQfUJCd4zRs+f+7R2XPnz8zzSkiGmpLAMYhArsyETsfyKCqaH9kFXRKOUlw0d/pCh7qbFBlNzgnXnFxLCo4m14W5wWVuP20nLRBN2jMi4ZLIk98mxUY7WNtYWSVhXJLX7dfvJEVHKxzPentcMlSlvW3F+uV0JDRaoXjlgQ9WVJaf+WGiD+CTGDRcXlnxiQMr51JJfLSqllaqExrdQkOKIcJFGlILES7QkA0Q4RkNCYMIbstoQKEEIYLGqNuQFwQ5Qt2uQ5gY6vQtCHRL34apEkIVTaZmryuDYB79Q9Qke/UaiLc4vpkua565CNYwJXUqXbK8vwtiOc60SRmxr3ECrdNWRct/XQD/JjhKCu+vpjPN1/x8o+Fo5DWIwLHNUPhD6cc3zAz0pwP/GTNTH5WWQKF0TNQQsGUr1EQ+f+EVFR4WFh7l9eJ5JNRcKRA3uWx7H3T5/BdEjlvLN0OHjVPFzohDd5KgkY/fkPDBdkY4NKnbZolpvLZMgouk87+0zArh8aOtcMHhgz3W2nuc7lecHgpJn/xUPknR0XOp0HL5qhdUvWvF9FYqTLXO3mP2qdz7/ekZSxbMj06av+DTn0nvv5/bPts6e4+HNORXEOE2DXkEEUqKacDLBxDiOg0YgRg3blK3tWsgyEnqtgrC+FGnAYgjvZm6vB8iZU6jDvGRECotlZr5pUG0sAhq8rIIFjClnRoc/Rkswe1rX6eLAlMkWMWxE8fpgpu3MmElv796jRNoP3EDllO1ejKd2jJrMazJrXRk/ZuoxNGsD22UYGmJm/bu35eXX9DaUpCft2//QNgu/Kk9OBYAAAAAGORvPYi9FRsAAAAAAAAABIY9HLkA16UTAAAAAElFTkSuQmCC';break;case'apple-touch-icon-purple-2388fa66883b7c5e6b4cf5c795eae8fc__aff407a3.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAAKzUlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOQBJz2xh+No2Cte2bdu2bdv4bNu2sbZtewfr3XFyn6v+/X/ZmckknTmnuqpmMt3ZVOXZxunznva8RcY6AueWDK39WN+fX935oye2fvFeFD7wlYv8RIXsgkMsNjXmO/LLrp89veWzd7lhoRqVaeJxOMQSoVnf8d+2funevPVFFZrQkOYChzdtpuJo+zcfxptOutCcmwgc3jIj4Tv2G95uWgpdCDcUOLxgicj84KoPpgULVbght9UeDukzFBlp5mP1h0zD0BgOMYYAG8hQ48vvNIZDZqA2YaEKf0JLOGTVertrk7Yv389/+i//LQPL3nnn73548+dVZRre7vqFP+QROGRA6fjuY1UdI7LQ9bNn3Akcc01XVWUaWhhcdIBDfKA4wm8IBxYeam79wj1TgQP/WGx63BQ4sLlAZKRhqvXiSOm27kt/bDr6jap9ny3b+ZHire8pWP+m3FWvvLbshZcpq191ja9c5CcqUO3yn5poQkOazwcjpp2Gw5vXZgUObDJ/eypwUHxHf5WlcISmot0FvsJ1nYe/Wsm7X/K8S2kp3Orw1yq5bU+hPzQdNdNqbIhYh4MV6dD6T6YCB2NTdsEx2jSVu6xt2/sLlzyfd2lv4U9s/0Bh7vK20abptOy18sKswaFmrzNsxiYNByUy1ul9OHwdM/mr2re8K5935kjZ8u6CgtUd/s7klwBstVuEIzY5qj6H+mpaPne3W8PReMUiHPxRL8PBVODoN6t4PS4px75dnVxHQiiGRThGdnwtNjmivgavrLlV5dn6ixbhGFr3cW/CMdY87RgWFhAZb10cIoTqWIVj+1epbMRjavIxuPL9t4Cj5rRFOPr+/BrPwWGYZdt7WFy4EAtVeLyKXb2mZWP2YBEO39Ffc2X88C/Ulfj8ZMf3Hn9rB6gFODp/9CRPwcEyhP9L9Q5cXk58vyY8EzMtGB4Oi3AEL6/+Xw9Rd0FdXOgsUZWnivdZhANvh3fgiEcSuz9eogUWquz5ZGk8mkgjHFNFe/57se1rD4oG+tX1wNl//Pf6ZO6WbITjyp+bNcJClWt/b0njsDJTdVJd7/39y4x4VG309//zrVwMXFiWdcMKfiftsFClrzSQrgnpXMNldZ0ytv+H6qf4tK/924/E9Zl1E9LLf1Ldhgc7D+tLWXxct/qVvkT9Ot+SM7rj61m3lMXvqS8cOz9cnKQTzAIcbV+5f9TXoyqEeiqzzgmmKRZqZZuk+9wCHJSe37zQiEVUHZ3c5wLH0hdcTn7jzQIclNHd31Z1vLbxJnCwZZ8KHJTp8sOqmqe27AUOgn1wPKQCR+uX76uEst4J9hE4VJhgKnBQun/5HCMa8lSYoMBhPcCY+GGu3EkZWPaurAswXvHSK/rCseoVV0WaYKMd+Hy5vnAc+nKFiJpsNGK99IWDsFORQ9pohM+oGA69yvIXXyGQ0XEhNWR4WUhduqVbRzjKd/QkJ6dO4/jCaKL6DG/CYSSMg1+q0IsMtBGmIclbMmKR+fjJH9TqQsbpn9RFF+LpSPv0u2TTPv0uy9I+GWbJ5q4lz3e7YwOpXHrFkji8lUr2zgvVqIwPNEsTxvWXB3d9zKUhg7s/UTJQGbQt1WQnW+2EYhCqQygX3QOFD3zlIj9RwZRUk3QhrZdGkba6B4tt7ytsuzJmGpKk1h2WiBtNZ4eZ99GTOziIoKFtPjdiJFzJhaSanB0Ple/sQSCfSSx2fbS4YnfvrC9simmhsp8aXqAvIdUCQmd79NNFpGZoPjc8PbJgirkBjv2fK5voX7SDb2Ey0pXn45/7yl+aj3y9kgnKshctYvShMk1I1EFzbtKV71uYjJqLtGDP3IEvlAsc9m7Z855Sz4rBtGB6JIQ/e7h+EsVAZ854y/mRuqOD9ccG+cDXvrIAP1GBaqnPIaBz8zvz1Za9XSZwUA59pUIJyNxvSPQOfLFcxXPYawIHZe+nS2fGQqbrjY5nzydLeGCBI6ORYOvekNNbEjBdbD1F/rWvy+FRBQ5nwgTZhHMhImDB9NNCmKCdJnAoX3X71THHvU88QNvlUeXRFzhcFGCMWLLx1FAi5gAi/NGGE4Nb36t8KgKHK6PP178x58xP62oO9ZO7zTTs3dZhrVtzsJ9NeSZA6gEEDj2kCWtfd53EOpV7esngRp+flk2ckcYpkjmd+F7Nmtdet/wkAof9lmJyWRwk535Zf/2frcSC0LWwnYuza7xtZmY0FA39Lx6HD3wlWBXnGBWoRuVr/2ilIc2RF9itW3GjiahpxUuv2qaLETjsN96fvtIEui6Bw0bb95kyfeGQjTd77erfWvSF4/qSVoHDRuNcC33hwE0ncNjrgiSnp45kcHSLaUiwj/2KSKb92mUDU1pIgcNeK1jboRccRRs6JUwwc4ZiXRcyijd2SQxppq1qX5/7FW84WE1HTKLPO66Pb3hLnjvJ2PjWPKKaTUzgcFBUzYlurkraQbR63sp2JZ4WOBw2f9csRzY5PsrwAEe/VRXonjXdZiJqQtKSs7R1zWuuZx4Ltu/pwHgAU8wpOKyEi8bCceLB0M0uf4ntqQfZxUUl1Xh6OBZOWHp4+0zgYP7P0W4WK0MJ74N/aKSz6R07EMfmrWjn5jBhPVsVsJq2msRzwMdQ7eRiG85PRMiWUXd0gN0vpgX/1Z9ZXI5yVi1NGLCQxHET5GvmIo1W3Idi2m0S7LPxbXlz/nDqIcFIXtFb+xBF1k3SDbAxRuEDX7nIT1RIPVwZ9f2GN+dmLthHIsE2vyM/2Dtnut4C3XOb3p4nkWCZsFstENT44k4brJ5QSyeBw3a77W4nOY2ZeJouM6KUmbHyeA7HkEqAMRNGJgqu0kIy6kmAsSvgUBGa7GU4GVBjmCT2IL2MRJ+7VJpAkicWnGotkxnjz9UeGSCJoB7SBNGtcE4n7i/lp0q7MdHpKfbnLG3b8aEi0a24Ao4kBGdIXfBwk8srLYF6vvYZtJCklExC/sTKxXStiW5l3etzdn+8hNRv53/VQL/CSQbswnQX+EabpkgKSB9Df8AHvnKx4eQQFahGZZqQWIHmoltxLxykjtRXmnD1ry0Ch41GvgN94WCmLHDYaGx6oTjVkYzVr7oWmooKHPYaYf46wsGWvQT72G6RuZhK0adLIdlQhkJKJUyQ7Ft6wUGomIQJZs7Y1tKFjII1Hab+ptkxXmd/Xu9+Ms7/usHMvEn0OenPcX26mQzUEgSSCRyOHdBEaKc7yWDg4/FMZ010KwR+usr5gUsDnabpuAkcSs7kkmMiOSNhcsA10iaBQyX9aTozvOnt+U5hgdyB0/9UtJHA4caTbyr39JG4OJNY4JQjH4Qe5wPJAYDh2RgBHOjS7MaCsQwWcdqaYrrAoYzc+GRDSHsOD1RVRL0j7TfF9IVDTUcQJLL1RTb79W/KTQ4IGpJjv3RrN2oUmVhoBod1I+89x4kXrO64+LvGkz+o4XQ+4kBRLBL5R0whH/jKRX6iAs5vlspanCf3r3bpQAYAAABgkL/1Pb5iSA7kQA7kQA6QAzmQAzmQAzmQAzmQ4wU5kAM5kAM5kAM5kAM5kAPkQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzkgDe9KFu6DmR8AAAAASUVORK5CYII=';break;case'apple-touch-icon-red-507228751d2170d047e72142d2c02390__aff407a3.png':$f='iVBORw0KGgoAAAANSUhEUgAAALQAAAC0CAIAAACyr5FlAAALAUlEQVR42uzSgQAAAAACoP2ln2CDYig9QA7kQA7kQA7kQA7kQA7kQA6QAzmQAzmQAzmQAzmQAzlADuRADuRADuRADuRADuQAOZADOZADOcbeOUe58yxR/Gfbtm3btm3btm3btrW2jWRtZG3MvM9TP/72O5vMTLqTuqf+GHRn55y+26iuWx3zGG+v7v7ukeZnT6i/d9fANWtXnL0AxgW3POQVBeKLHILJvvbOT24O3rBh+elzzdEoRmGqCDliHNOjg52f315xzoK0+qyMKlSkemySQzCQ/WnVxcvQ0mEb1fmR2CKHwJru/Ow2WtcVowvhB2OBHILp8eGmp46mUV00fpCfFXIY32coZrjMj6ePsS1LyGEwGAI8YIYaX+4Qchg8A6UJPTX+hJHkkFXr/12bVJ67SNfX9/3dGh87eOa2b3n5TFWYiv93/cIfEnLEyIBSffnKqow1PhK8YaMZyDFU+qsqTEUHg4sJ5BAfKI7wOZIDjDWXVZw1fyTkwD822d8h5AD2eFd7f2Fm+7cf1r/4QMUt5xecdWDucTtnHbpF+j7rpey8ctKWS/yxwXxY0lZLcstDXlGAYhW3XkAVKlJ9vLvD9hI4vGk2J+QAvUmvR0IOrPPTW+KUHBO9oe6E72ueuC3/9H1p+9/XncsV46cKztiv5snbuxN/mOzrsV0FGyLOycGKtPn5kyMhB2NTfJGjvyg78MC1GQds9Pt6c9OWnhp/IvOAjQMPXjdQnOPKXisN5owcavY6wGZs2OTAxtsDsU+Owcri4KM3pe+1Dm0WFUvfe93gozcPVZXY4YKtdofkmOxtU9ej9fnlZ8zz3+Qo+cUhOfijsUwOpgKFZx9E82hihecdGl5HQiiGQ3K0vnHBZG+rug398sx/FR4s+tEhOZqfOzE2yTFQkqtooZsVnnvIQFm+PRsQquOUHK+fT2FralJNPpqePPI/yJH/tUNy1N+7W8yRw7LqX3qIxQXNoK3xeQ2vPmo7BrMHh+To/PRWnnR8fJN6MjXcW33Fqv/lAHVCjsA168QUOViG0HWrNtDcii48crK/13YAPBwOyRH6+el/9BCFP6iHI4F0Vbgv7T2H5MDbETvkmB4fyz5iGyNooSzn6O2nJ8ZdJEdf6jv/8KlfsMREd4N63v3tQ39/3pvwSjySo/L2iwyihbKquy93cVgZyP1SPa+7cwdrakJt9Dc8vD8Pu394LO6GFfxOxtFCWSj1V7cmpEPFP6vnWPv7V6tXU/2dVZcuj+sz7iaklbddaC45qu66zK2lLD6u/3pLX6LeDpf/0fbGhXG3lMXvaS45sg7Z3IETLExyVJ636ERnrSowWpsTd04wQ2mhVrYO3OdhkgOrvW1ra3JclTHLfS7kmNfJxlvY5MDa3r5UlYm1jTchB1v2kZAD68/6WBWLqS17IQfBPjgeIiFHxbkLK6FsLAX7CDlUmGD45MBqbt7Mmhg1P0xQyBFWgDHxwzMHGDc+dkjcBRgnbLKQueRI3HwxkSZ4iLwTdzOXHPmn7i2iJg8RfORGc8lBNKvIIT0E4TMqhsMsS9hoAQIZoy6khhmxLKSue/4+E8lR//LD4cmpGV/cHE0sK5ZTMFjT03kn72kWM9BG0CqSvMUPTA0PFl98tCnMKLns+KmRITfSPt0RbtqnO+Is7ZNl1T17D7IRrR0b68+DVM5dsSQO75lVssooRmF8oHGaMK4n/ffsw7fWkxnZR27bk5ngWarJAFvthGIQqkMoF90DxgW3POQVBWxJNUkX0v7dRxn7rq8PLTL237Djx0/5MElSqwVQcLR9+Q7zPnryKA4iaGjbvnrPmpqyNYSkmhxrb2545REE8r6GeB22VcNrj411tNgCI1T2o0119CWkWkDo7Il++sBNSM3Q9tW7o831tkAHcuSesOtwXfWsRVA9XV2/f8M/d+UdFxeceQC5N/7YcH7nVKAwVUjUQXV+pOv3byd6u+1ZYjhYkXfS7kIOb7fsmXtGnhWDacFoSwP+7P6CDBQDXb9+1f71+y0fvtzy0StccNuT9huvKECxyOcQsDN9z7XVlr1XEHJg+aftowRk+gOJXt5Je/DZQg6fgn1yjtlxrK3J1h50PNlHbccHCzl8jQRL2WGFUMovtsboTvoxebvl+FQhR3TCBNmEC6X8rCEtmH6qjxRy+IEZfNWdP37Ghq0dVfABHd9/rDz6Qg6NAowRS7Z+9qY1OWH7Dv5oyyevZey3gfoYIYeO0ecpO65YesWJze89T+42bzc4LIu1btO7z5VcfgITIPUBQg4zpAnJ2y5LYp2G1x8ngxvuClc2cfoLs0jmVHTBEcnbLuOBNCEaEN1K4haL4yApu+bU6nuvJBaEroXtXJxdg+UFo62NUyP/iKzkgluCVXGOUYBiFK665woqUh15gde6FR0hoqaETRf2VBcj5PAWtJ+50gS6LiGHh8g5didzySEbb96i6s5LzSVH9X1XCTk8BOdamEsO3HRCDm9dkOT0NJEZHN2CX0TI4YMicl7jsoEpLaSQw1vUPH6rWeSoffpOCRP0DyjWzWHGXRJD6jca33xKf8UbDlY7KpDo885fvkzdZVU9mZG662pENdtAyBFFUTUnummVtINo9eDDNyjxtJAjyhiqLuXIpqiPMnxA4TkHDwXKbN0goiYkLdX3XZ20zdL+04LtezqwkfroyZeFHE4iiqdHR4gHQzebsPGCXnOCXdyCM/dv/fyt6bFR5x8v5PAEzP852s1hYVhC7DH/0O5KZxk7sg7bMvDQ9TQ2nHCerQqyCjk8xN+jIvpyU2etOQt19mYlNn/wErtfTAvQnznU41OMs2qpwoCFJI4fQb5mzxLU4rMx22tIsE/abquPd7ZFHhKM5BW9NY7tvvx0+hg2xjAuuOUhrygQebgy6vvUnVeRYB//IsHS9lxruKbS1h5DwfK03deQSDA/8F8LBDW+6Ine7GS1dBJyeI7/3e0kpzETT1szEKXMjJXPi3IMqQQYM2FkoqCVFpJRTwKM/cbMEZrsZUQzoMaySOyRe/wuEn2uqTSBJE8sONVaxh+Md7Q2v/8iSQRFmmCGboVzOnF/KT+Vm1AetuSfAvdfk3nwZqJb0YIcYQjOkLqQB4xcXq4E6g1WFKGFJKVkGPInVi62thDdSvL2y2cfsQ2p38quPZ1+hZMM2IXpTvi+vyibpID0MfQHXHDb/cd3rZ++QQGKUZgqJFaguuhW9CUHqSPNlSZU3nGJkMNDkO/AXHIwUxZyeAg2vVCcmsiMpK2WnOgNCTm8BWH+JpKDLXsJ9vEcU0MDKkWfKUayIZ9CSiVMkOxbZpGDUDEJE/QPbGsZc2LoY7fYhsH8Y7xKrzpZf2aUXXeG7T8k+pz05wT36swM1BIEkgk5onZAE6GdejKDgY/Ps6ML0a0Q+Jm05RJauTTQadoKQo6oy5k0OSaSMxJGGoK2JhByqKQ/bV+8nbbHmtGiBXIHTv9T0UZCDh1Pvml84wkSF/tJC5xy5IMw43wgOQBwcqCPAA50aV7TgrEMLuK0tQWmkEOB3PhkQ3A9hweqquCjNyHttwXmkkNNRxAksvVFNvuUnVYKc39k55XJsV/3wv2oURxMLIQcZoK89xwnHnz05vKbzim+6ChO5yMOFMUikX/EFHLBLQ95RQGc3yyVjThP7i/t0oEMAAAAwCB/63t8xZAcyIEcyIEcIAdyIAdyIAdyIAdyIMcLciAHciAHciAHciAHciAHyIEcyIEcyIEcyIEcyIEcIAdyIAdyIAdyIAdyIAdyQHtHp5xFOjNVAAAAAElFTkSuQmCC';break;case'logo-de272eb4bdca9c6fffd38c073270fb1a__9d7e398f.svg':$f='(]^+JbP.FqjXYdorFxH%oTmn1#,Na[(-^<}T{`+Ahl-RItQoM;{4bK}l["$V3F6U&V6Ey@S8#w=t>3kaN[hLow+fWEUH+K<LoXqyEy6JupFy-JyK4S8q(7tl96;KLl/F|,Cz)p?p(B)[axu/4u77-)nvU
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
n9lrNOU01c?:p/5y16+Zkgo}`M)D6xm>7RiQ_b%p%ocllip!0).myrT"w[53iGRZBt8z<.d6p9("_WYy1v;v.xBx%3c3&hfawJgMtuxeflyK1.-:wQo"f_z&)W';break;case'jush-b3a93b18444da26820ff61746521dede__72e4fe51.css':$f='+UEmPb3V?!K0u25Dm[994[Zg@N#Q)YOC=2R_hE~4)=>cbdia55M)rQq_opI7=E.gy$2_wn3[@yoG6r~P5/:mrvY<e>#2+8qezLLv^&nr;/Kkr(>?R(rf#PZ<Kx
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
)yZA,ICx<`.i#E%U;lo*kf6u&LQx+!%1t]iP#G9;zGT,4U2"ha>hB#am`y1YU6$z!l#C%';break;case'jush-615bc0b9720a1de8edd2c6876a3495b6__aab91337.js':$f='(hk]`!>p9CvwpHP(hq[*!NJF5FML(97K>/e-sd5Yd_qN;*HB8+(1wUgZ|7~w/mVWtDMgGM~htv^jmBm4eb;y*03o(V96q=%wGFJ+{#Pe*,NlHjy7BFQgK&`=9x
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
d%=.2Qw3Bb';break;case'default-blue-564b3ff62703b0741b8754503c621af3__cfb00ea1.css':$f=')erWG6KZS1.Oe@H#WA[QPB,hP./GgmWDCog8Ul^<=Uxqoj!2vm%U?P@X(i!rqm#y~GXsKy9(&7`$LbEK)Ys)yC1RCVp3;B<`nk+b?X>]FJ]4sICHxD~6CmHVChb
Us+W>=zehc)xx&"9Yu9wv9r>1H.4D5gF?Jj-/K+
dY&Zu^55(0Rk},Gl_nX)Echl.W8@nGiyE/s`90i=]vPG8`;E;R30aa7<b@aIv3LTDEEJnPV!Xp)qJdvq|mKA)^=i31Y<^kzJqpBDM5iar@mXU=_S+?GH1hlN6kHbf8JTe?V!)yZXS7xqwx==<=Lr(U3yo+Ix:ZVP9Tr.9B,W4[D.PeiNDFiT@=_LoZ=;`@rR(p"SE$Ea$b1]!%aMxw_8{w(5gFU`#=!Itw6T{N/7L3N8%&m6|O}.PrGhy1P01gH:+`iM&(fFGM/@xso?,CwRq/xsML~H!]~UV;XG}hByeM^w;jxMbiAtQtu_rsw:IyRIivrSRsPpbMxpdn-q+L921ELz&H27|l5qjXaklS8m6ycz(T/s043@3tQu2Ktw6K6yFf]fkz#h/MAo%=FKr78u)16!L6evk!N_vMjToz!eGL-u{M|yUY"w_7(v^`sw;5Jyff%wqv|y-2Mnye/MvxjMwuzK{MBa=eIyfcTuz(o!OBaS8qJc$q]n7X%v|My5RJ4rl,W?Kd#^l=mJprF-vsPsA?8$hn-<Z.I:%mIP]5qo#.B)KiJ[D&}?SoTxDG&E9!wZ:x3MTj3wkkvpEkuY?D6
Nhq.3YR*Er{s@tZ"(o|NVN0N~/k$+iSK*C2OX9P#XVL3y5
d.j.C{=8uT5EGu-=RjMhe/J9R{E,X$f$kG1d>1b.qd2=$fHyP)/,C<tPFoTtlVOo_h_G?$dzV$1m20bW(hy}Cu8n9vFB]g8Be
a3PbL31Vb;"l(Kepm
#qF3qWK.X7e}Dw*:@N"1DIVD$+7}FnFq;nN-/1pM`^-D#0TZN9#{VTE+"RcL8Tkx,uXl`#aOWK4P*LW"V:EUn)%2!do%5ddp@(]#lBcyX25U,^>/Y7^[VG,aP#">e^i=y,@pww<_`?h&!gm?W>N+E4b;daa;/t8.!s^Guod29YEb=C1P)(:_NwTQs8-~GjAYZl)aj`l:ELo5qz^wwIE1q/ollg
9o;>*?KRYx
F%J,
%qyLWvZi[cDUHlfqq!pFGF$?5cjuD!&Xc[&`6YpLnTQ/3w7RDhU$
:@dU1NT1l+T5.,Yj[K#h:!hF9!a3O1!b<eT9U$qHG@G(rEg`<p#~bjRW4n4yjX>?@"DV%aJcXxWiF|_c!t*Z)9d*N_fH=a-fxBJvPa234c1[7L,w_Wg
#a#eu
+SBm%.l`eCpl3%?(D)<
_s5,;F9q1b%sJrCq:-!Wp5!7GHt
[%FH](_bKiStnLSpV2"hb/U-/
p~1m>g>,%A><>hlVwW1Y$+`:
IhxZR-uVn#&8-0/*F+8b$tM)6lV/YA<[=xJZ^sMCx2Z`ISXQX@E7Dbi#v2*tw:xUTNlc-NiGlqaBD4^Vc1R)z0g(L@]H$kW!hYEGA32?E_btML:M6/cX@"|j9`d5Nlt&s>axb`b_NwfN-q50kn7;KyzQjN;2yVtoR
QnJfynP2FPCs0P=k-e+R!b(ooeISOim27RBm^^.]lPB/x@<&0u2eUxCw8Oev9jkH
Wi0#7IALkc$a3.u63zU_8"&wYc)SuEg"`s,$"WZG+}jZ*pWoi/3-Z@vAm9`~U45NTB=&x..)`U;70Z1spbN2br&l#rXG:yqLEg^IH_bnX(0.Jt(:4Amh4Z3nHW,IO194svlTo@bbffU,xlt0I"BekXlyw8F)8g4*CK&?9K%$%Y8?tdgq3=fsYE_dwE[*`$"3XF"0FO8|lhVnGk?3!92I%h#2e<Z!!.OI6Z38^N0edM?D
%JxG:8rfh(%n<OW%/<VG95=5;;N.E?V
9)O7Z[qycV}"Wo^RNq?v%US$?dac<UkWK9`>H*)k3hW%0q5(+,ht
)6>3Q_ge4;CMp_Tpexk_
K*d;c8@ka/u_nptDIxd1hgmvHBWu]I~d+
#Y@L)d.=8Py$#*^KSe+gQ[Os:B@$P,
gi<p>X->>BOJV|2iW:bEN*+RMQsGYLOG(WU,s+&oc))mW#:jZC
H)W,ix5sl.vh5->T`dx#t;I!Kq5`#E=T*pK"UT+IAVpB!T7y`(+V`&F6fNx#r3I,knE9ZPr5L=/l*/%qVd#nvs7]W$]43BfuN12ikx>R)(lZXqpDxLaV6/I1C9yL6x1-D&Fc~)c533p
BW9U?5
9W8~.psPiX"#Jz;<Es
Ig69c4v"N
CXPm">ScL*Eo=y7<|BDw=3oujZVrtw=.0lJtn
:Hju;+VY{s:w3nJv,$,fDj_GLnTpH.$e,kP1IPCfCx6w[P2f(P48jNX7<mu"rSdUBF_e?AhcfkEQ6K#L(*4?4Ji^GBM&
8jLsp
8]?cNz?K7>OlhGc2(;A@-pdD*[1q>AaA(Q_dI{]K]c<_:34E4sM[3uSUO5xuG<W|;<s&LoVTh9)"sx5tT?D(=>*`)?uMN
OP0hhD1tcw?|VBY^sOwd$pb56x7(;RFf%Q9JZojIOBjzPZn*ll+]C+"T8}T%MIwmK%j6JYLMVkk-1&l!x0Tv*k7x@sv,;*nDwCHYb/Kr%Y>.#W&G_i63&]N0>0&OG.&+(6&xJ&bdOkdaG!TeBs$DOO<&9p9l44E!dqYl)oVC2f`!**q:l|A:Rwd1j$EILZe~vfT&49Q8Fc?tReP0CT-$
,-M3ER;0:n/g:H},i,!3+SKZie{4Ju#
Qr?<0G"q[
b#m3=6K;5JIdMsy3&R-R1u!C857:!ihWt8O=]cwH+:*G*EF)ccDBZ$|Mx,ULjQElwv]#<exh0mqU*aVB-O(yrH28"v$N`siMUk5(|:z.~XPA_bQ&#mC`SSp4{9MALPTuF8cI{PPhOP
lyq%VpD!z#SToZKy/.@u/EPVVgU^<HC{Z1q^W2Pt<]EC.j&30/Vb6yF-(=61oJ0oS7lyYft#Dj("?1UUTamlPB_%:-yJ?mQPoNDXWr@|szC~GZeY)^$SjNS"d@h4<&BIuc(VW%,^8;oe(;ci,J,YNuDIQI8Ydk)68HBAN*B~HDqIGz,lR"gBZd-%(qa%$;oW&IP$tpx*,DLJk/*c_ieW6m"C&V-/qx&xcT?V0v+6oBbN80Q5*9K2d9M;-XM[d+"1J}?RK]>{Lik9PoKx0&<
5WTJqW6@nL3V&HDsIZ)2D3E/w33Jjlv,+bJQ>;rzcd03ATehiyBa3[AF
"Mpv[xu5#?%6
5=f2NpA>;f),k"xZqA&s%-Mvu+&V7Lj7s:ySr9Za^&k@z#Em)yD82u&LrBj<CesMfp*44cl1iBvYF
HHGdjHLnVS
`W#<m`q<jsugJT:"9b^9Po:]dwGI}V]tx&>k7?kC5VEvA*!qoe~!&d.&`B<1u$vNaj*rVvA,[U_fp!m_<l@xPD+t3$k<<8"*I^B#(-uJk^_8h;+p6Hpw#Olg1uUEvc=m?9YvgfW.-_%yp9!C~B,C_J?Z:7.(-/GN&a~Y//K=vWup-=s/;YzFBo4O0IK"R,B#dcsLnuNJ7l5<[*M]F_iwH7yLP*@_Cwro$f%0f(tRB6LX
PAo>1Q<H!6PRg,w~K##/Vzar>nW|Dyu"W>/,OKL}CNJrd2!4UGZyX+
rvcIR6cr5cQny?MP/j<G6u+d~UTiQC{[^QLT{NK%_[)z"q;>#$JA]6(<zBE=RV*G:pks/.jx4F:C&h72k"rAY#u9:-5>d:~s^K)#^nejbPF&}Ew5?)Ka/G"rF#0B/*
e&rfp_%.;4;V>fc}.NsdjKy+<<G>m&gw@R@0IL6mP+4s>NO:<`(weG!*jPhs93I(wQV{nWs*ZDu9pfls.f-yuO09cRON["%RZ&SA)b9j"KuQqZ%A<XiLdmx{5a<$K?Y1<?75<w5l@[DkpBZ6L)wyMvbCZ&d[>,jQ>L>-R5,`cB"|`
YETNd3J&W5#v>`"j570("i$XUrM)rU#M_OGp#IYkc:,saUf&${T.$k(UO;n1"e(lOR3KDJ%"-jt0yLAM02)C;_BR/BlW_-C08Rsu_WMeu~jVo{d`v;#u&4H_f_D%4>)S!QhU;`+Su%ad)<N7"sSWf|e%I;l"^:!ihb=4e$JvC6/kA_$/2Hsz$-Y!sj?y4%khaR^y
5K.3BP#%)e}%8dTe@];J34sBRQsn!R<l<[Uhi$>G-mJcvwPAA)or"bw
5sS8/Zq+1l=2;Yd3a;bCo*gbD
($n=Q]zOkLS
jg}01*dEnvp+DV^(
t0hz>AT}sA9sk5nV@duI4uk~=8Q}"AI@_f^@`}@jYp=p9my7?:a>YMfE;`.iV.UZeAsJ!A*)NMFA.wHGV8.lI!+Zy>J(0iJ2sW?GM,e8@(OEL+li]<q7eWT^++G0g-U6J^[FSLD_?X:"?%%}4fX@*$a
h}B}pflz0+obTIuFuKHC2&0sM-UPjLj~NM?_b~&Z^3CNvSJ.2;$w$[AZu5cN:^<Y)cx7_Qv]3P!/.|U``:Uzgn"?tQN@ZqirT("HvaP"_EID_Q1?Kp5(mt?2khbE,{jtFy]6:d^TO{u[_=Sn8WFWLon~S1W:14uWT+C4"?J!6Mp34{H
;Ig&M_5_1QV+4x$6ENAW${3~C)di4a7q_UPy$iJT)Jay>&KQtd@OvF<iU<3/
kHtFg0_UV/w4iH.JHQU)4C4Zejj;xY7&HY~E%5/Xq>/Ak0<2RO%9OlN,ZBK$2MTKxC[lAPTg[@Ox=d0&Dk:g:k^L#]<kuAH4.7y_T(]Ou*nk".5XZ*ma9$xQk[%wM_q`]M7)9IrDpq8QAj}>i-1<~Q$+.8PRGsLbf>zgR@vOOV/
~!)a!-R.YrvpfRTeLR&9/J^2TWCc#Ey9`cR)PWm?je4eji=`>8KA94R2`h%e!!wTF&A$5!W5RxiR>5&m<]`.J0*@U,1FD4ClC`dMU%P!&24.
E>L@w8/@jnLRV7e;e4WSqY+]wcf}@f4<[nDBX-`5hASVn
c`JX[B:9oZ&SY<=4Rcd}?tRPT*`+/fq8B.j<+7W?/:JGO5,VbMvl*db=;TcQE&$ll`/qi0Wl_lUKB>kJE&$f1qQ;Z2#[s|v1B&aCA6^C%Ab;,Zgr!9i_5/k<&[?fWzvAl9fY07km`+r)KSROjv7wB6rO.]
G(:K+KOJvrMFDNM6=QasPOT8#T|Fwm*TZ!0@P7O<3.K%~lmU[h8K%q+i7*V_cNTfC/r/ToU=bT)m7d|Yb%)X:FHA6"{%gvbtNW1in,Le-D{ukplIKQ+8.h%[~PBU6Sf9O^H]REyV+rOHN8X_0I2!kA},=:0$My[)ni[B[F5*HNBo5-0>QE:.g
;-|6p2E74By>7&[
K=RM!&IQ-yT<gOoF?m#1ELSfD,nkKnqj=Kg%&RF)ZofO,rm@T9!J?
I]$6)d?oAM}9ck587uqRA^eRx`2;<fMz([x48$"$a!^H"/0A
y@j2CWpwO7.$uv:pf0]}<EXZ_enjS=3%+cF`$c9P$7Nd2ua63$sC:6XLA<pem{RnI[v|w%$ged()`L]R<]hGr@rWtM*,,`meUVL2x<2}K-@c?Wkg[_[WSyCLJE?iI}.w`O3g*<(4JEBf9c:<u:0(Kv<CveasilY@n9s5q(6(@k6~=~h1D)tH1Gs^#tSBBohbLxpxe<@_t,$]:V]Sd8@Khxs|.9O~aU)Sj(jGC@WJJ!IO.m>sx_k2,~l{8{Y"314:Oyq4XT^{F<
2EXX@_yh0c?Y/^k37(#,xx_):xh!|)Yh;d(ifYmYp_N?DDd7Y;fdPCBFe0cy1i]N]TiA<Tdi?HO[b[Zk.d7n&tlZ,?c!7<fN
^4Mw8Gt)"@CNZ_rFcC?zfJUIKo#=^UP3QN5W@bq_(W6Pr!Xz2#&^[Yk>mg^=+3:F1^NdYPCcSDjY8j&DC]1,_{YT"J`l;oponE;!9G%I2IO~
Z1)
/:pJ)*V"zEW]ysHV&%x+^pt>.jy[9Wm;5V[e<J2ePtOkbE>c-Op4SO7-&;~olwbs*E^!URaV3/{S]E/xRxBIqBH#F^14XT)wPU%#.qGQ?^Gkgn5p1$@m7j~,_lT-
FF
K_[%|@_5w(**)`Ep3-=(1)0oc94_7rKYuEUcuiO"reerDMYC>Yik?
I.t=4cM/,X{JO@N"Rn+,|g9a0gb0LLRqK&qKy,6&1)|$DSg$(
:4]&pUhRwBEIZ?xHW^``YN9@T5
)g4P;~D!L>1wQS?XDHm*pd0muC;u7J[;H>-^6fvdIEED/8N
DsI3.U>dpP2;vG-0@|RxH|?k/W.q,"/:S*p[eqb~>JsI6L:xcf2:(6FX<d,w+8ZW_h.&1}F^I8
{%OaH@AoKf%y;j`wr"
[cAwS=MeYhx(k3HsRl&9c{0&.L$ukAq90=vbrHKmh$_t6awlw.xG#cmw]IY,L=VkI8m}pm!;m~0or^Kjqh*o::EeW3rO^l%<5yT@DfR`I!JZllW[`:Aj!Skp68Us1@Rq3L`Rw_[gB[!HaOu{AtEUBqpB.e@

IW`^rBdxd';break;case'default-green-8facfae54345a3eb358848ed4141060f__cfb00ea1.css':$f='$erWG6KZS1.Oe@H#WA[QPB,hP./GgmWDCog8Ul^<=Uxqoj!2vm%U?P@X(i!rqm#y~GXsKy9(&7`$LbEK)Ys)yC1RCVp3;B<`nk+b?X>]FJ]4sICHxD~6CmHVChb
Us+W>=zehc)xx&"9Yu9wv9r>1H.4D5gF?Jj-/K+
dY&Zu^55(0Rk},Gl_nX)Echl.W8@nGiyE/s`90i=]vPG8`;E;R30aa7<b@aIv3LTDEEJnPV!Xp)qJdvq|mKA)^=i31Y<^kzJqpBDM5iar@mXU=_S+?GH1hlN6kHbf8JTe?V!)yZXS7xqwx==<=Lr(U3yo+Ix:ZVP9Tr.9B,W4[D.PeiNDFiT@=_LoZ=;`@rR(p"SE$Ea$b1]!%aMxw_8{w(5gFU`#=!Itw6T{N/7L3N8%&m6|O}.PrGhy1P01gH:+`iM&(fFGM/@xso?,CwRq/xsML~H!]~UV;XG}hByeM^w;jxMbiAtQtu_rsw:IyRIivrSRsPpbMxpdn-q+L921ELz&H27|l5qjXaklS8m6ycz(T/s043@3tQu2Ktw6K6yFf]fkz#h/MAo%=FKr78u)16!L6evk!N_vMjToz!eGL-u{M|yUY"w_7(v^`sw;5Jyff%wqv|y-2Mnye/MvxjMwuzK{MBa=eIyfcTuz(o!OBaS8qJc$q]n7X%v|My5RJ4rl,W?Kd#^l=mJprF-vsPsA?8$hn-<Z.I:%mIP]5qo#.B)KiJ[D&}?SoTxDG&E9!wZ:x3MTj3wkkvpEkuY?D6
Nhq.3YR*Er{s@tZ"(o|NVN0N~/k$+iSK*C2OX9P#XVL3y5
d.j.C{=8uT5EGu-=RjMhe/J9R{E,X$f$kG1d>1b.qd2=$fHyP)/,C<tPFoTtlVOo_h_G?$dzV$1m20bW(hy}Cu8n9vFJbVO$9b.ub;"KWB2oX,?_)6+1U}$3E<Da:0#a.F,]"x!RPAw[ir9Ye;+pZ<A:"3`p%|gBd1N_9ruo$X]a&W2mAc`Qye;t(*FcX;$:ibo*fdDtk-O<(0chZe-hl.P{Nqb?g4<YWA06$k/PP=iW<b>ZI5vLjwW/XXPQ;ILIId]{*j/>EVsO!i7#]k&Wj4io7m9yB)wUN!?Lfn:C1(&~hfscsGc`2{@g_6RbIS#WWPOOCSC3
MTR`:%PW^;AjybpO?;7
nNT6]BQ"mX}B_TfYK<&WwWH<;_X1@0f=MAz<j.[etGKDvPTr@ijx(Fz))SN+zJS7W3_-MH6>*,Tgj2}A!3v6ref/B#[GHfdW_X@[QtZn"9Gm6FQHq27Z_D/)ws$D`x-pN_d`U"_T95_!z0H<Q#wZ)^=G9L:wSp^veoZ&)BpZz)W@J2cbD`LT5Sc7O!T*>:`2fkhc8F"XX)9n6bI<2/-oZ#34(uah2#/LbVhb=4/Ud=Pp5&GL$/TJcF0K{@^nj5p6>WF.+09Q~Qyk=C@=AVZD">v05l@2f#WT)Ng1j_!wR=PQrKbS/G@3_@(hzFA#
?r-[Y<#Xu6dX@VsC!E[Z@T.AeS_oX:KboLD5Um=tI)^GG6Skq|B#&(!7fo+r?d6}x;RUn8y0ht2?,sDKQ,b&o~4G^AD3FmTQ##9-^S@PXsx
se$#(abX!kYDuIP)Zlx,G/X@GN:v-+b=Z8.37In3!3uClOc?]5m|FpPaN-8FA]:Y^MIp=B<iGHOReKQBpYcaCeLw+|,39"C(cmsO,ABM-OQ*LSiW+74ij!2SXm:Vxe+gLG>3r]j!<C%D4>rQZj_4As`>Z,D&AG#PPtnf@5dXPjt.G{x[9zf7j<^yF/Z=9dd{-N6t"Sph3y4vobPz&+d8Li;(d6sL)Lux,5X#LV]g-Vg7%kkq"bRB[Q&m"{^i4$N@!<4WNvyb8$%$oK#kYC.RUDaAlehdpLxTt03q68&~sM9J]t.@xf]O(i?SQfH:e_.r7**VmKEOT<>
j{NP8(Y97VB`SfArsMN5s~Xu+[+5g_F-9@A_1#+xm
Ex`@<E0/JW%wlqTF9D,qJ)(5<e.nY4
s?K!_gJ1=@9-}X5U?
t&{o{P55Y?!;+d.6v
_kRro2y9;%FQ)!PYCO=p$P<7:gWQ[2^ZTu)d}n&3~t$]Wd)4:RW/38=Xg#=]n_1$)6,K+g;>F4y_30*bBo.jAFwYSN06{mdC&&.NW_]^oS|*L%!A`1x+byc$6Xrii(LJ{_k#3=&uFZ{6}ln6P-t[nQ_j:]W?<h!cn(e)x!zmex+cAP_z$u(_LY!`RWIqmIXR,U*;`IY5WS3"N^]O|wEiXU#_P?v<tCJU1K_xhwGxI0^A1pO.nS]@|
mWK)Z"PN+2?%@0G/Q+sk]2G(gee7{Gfe.h0#d_Bs8gpLfA7HHhQIVxh;`!0)|%U)5dCFB@9O{uVeYcGd@)iM2VY^2KBn~8^`AA!l:k|(K+.fVW3h|$(0Rb=uG=e&pP[?Q_}@qocr!/WEJ@<wKAMl5u&
u
X:iOAT@aady?CT;`LZCTw
rfaF3<H+d-GCo]$"$n4F|f$5L^^]{we
6mZlEO;84CC(1bWVgHACTS(JwNI%hja5mfREtqr/7Zh>V$:PQ+vPsfE_?tK#I/dXcZ3"6-K+,Z2ehCj:2#A`V<@@zRCG|U61288)X2Z*t0::i6hsOxj[ZHGGgT}Jdqn<ByZ00viK;P5X?^5$6ez9u4IH25<(UVnI>k>^F%5+&ZvLaGU:+;[WvNVp$4EJ|G
v>A_XNJ"
8Zq+yWy=G#gf~$NJ0O+u!m^5FAPhn%%%Oklx
ZDi)s{YW/v4>mT3Uv,00Nc#K0r!jQ32=
K1]0/oIZpJwvr,XS[`zq|8b18Y,I4s:>8
V$oGY0:^F/&9]3&Mt7o1>!wWmc@(!O70v%ZowkeY4KjmEmxajlY*Fy*vEQ`a*/ccew9]nM>^(O?*5]!BA1,nKz(qG3Q4GuSW#:k!Vb5R5Q&GrE(EAJ|(Zp4f=CVFLAI5i#]v8VN^u7>4{(>C$axz(3d+aeuaSsIre06e5VJ4hG-t+>-%T1Si
[NOj.8ZQ
y+?QN<,-h#:t25?2QiCYs4cVpm5E%%+vt0TfUmNk.8T5hShT#<M9k@i]+]ye+Fh?0h,*KN1J%[gEnp/9JlH@6.J:aeZhV@|MqNWb"/"8fXp-.7Q4!wNHhm2gt]HLx2V
V"dc3swis&{V.5i*U8gejL9Sx;GJ2R9Xd-D/m)3%,
gg{c:A$B>@cDR&_$+BpJR(#b&vEdA#%!?p7aN!]Ox;?U.#46K)_]Z6n>REzL8eUCNXP0v>
X0FzhRt^?SLERuUC_#f+F%n+EZRk+w(Cn,EFl5!`]mcDT.wMR9MZ%ce[%N@uA1[)OHJngTf/-jw@^WPmhny4P+hQE[V2iq_ty4djouHZ#&Uw3/Co8AV?(7wGh.wzF
bQ<}`9b[=)=.((=fCpau)Hb?eD5m:*)^<>SQ0qYe$6qJ(r-TN>W+>B*K$Fp:/%N^Z-l8$9Yob!q8%[6X"A:HZ=jjfg8f!g9B"AEwpAgt#.]Bu=SJs;Dl(BgF%S9L+:G/&Foyp52q@Oh/^pn.Z"h,r6MwU:g1G>@GQ[6lZvHZ]uKakmN]!C&rYi6OU7CE]9>:>a3QR"EY>v=Vf.,GnURPjxRkw*j1*.(:Hs$Ow`K>$>Fi5vx,a&BDN/S[H!wYl|),NsWb.fya*Y7/q0re&A5%wLQ}_w$_?=97TGVjrHdA%lOC^*3HV5DfTFADPiD?eeuucYgK"oRo2j?kawLLJjulc!O;.G)+a]w}xn0,CxMH;vr]gzMExsRnXiI>[l!R/vN_O/*ZiZFO_a(T,Js~#^
~]3Vvo#F*nF=@6c"$v4tfIT3yEU1R3QB?)#Qr-l/V:5I>fpS2^8>5[6s6NaeDE:(~J8B!S%yul*l`8O`^#%Izm]4mbKGo"[)jy*dgJn647^W
(V[1Ua<72)]gQC=$@0@M]EmKYG%I?vrc)d^/48BAU"E8J)"v0WQ641l%2]MgZGvb=F<owHonD<Lt6I4b:T>@K6O+o@y!?o8H,R>N<.OZ?9tzE,;3h5,@P
r"klfK:KPr?dbBOe?/_|ur,XaP(d369ACUHoQw:LK=Q1=_e$){:GV]g<t-/)]<>Ifsm>*02^Qg!.eaXKroN@A{A8aMRT_:$.(mu!,&
Rbt!jYP(WH00c9qZz!a8X"{<x8s:ddys7VWR}q!*`hA(Y"iN!:/kzfNO}*X`&Y-Rlh+(j.;pzoN=#l%)o3f,81G4MPzVdmJlQDSYZRL6jw[`<`aKXqfoh!<p&g-(k`M-_X
6NYJU5s
s)7!DPkYFS0CCz$j$Qc^iGZ)>gB[]@1O[{.@j=q2-)GN@=nQ:|f+N/]mVYjEEq:1n@dMsfH$2:<&sZrhoT=!9qX,PI`&oOw7/aV!AD-eimc}JwRUu=^F2>3IYTw3b.cQ2UB5ctS*BDW>ok`i
HGWsFMB<)0A;1irRd$+<|M|1EWO9Slrw-a-^s6(WbHZ&ybds,B;dSLLbA6#Bn-V>t<&@Iszv#D7
j`V>Pec.0:AWd
e,45x`Jk"oIS5p9N$L/dRvGQ1ei>lF,bHm
hxa1P&W3o"B(+Z5k+3[g+[2R/7e=^l&L<e@aiZ3
s@T&W[;tLBw[L-HIO1x9yW_WHk9Y@
3b:y^SwRAb@bPj6t6J&n&ZQvtLH;o7:w2k)Vs:-ho{vDoIhtr}wB.&Q{eK8KfuimAoRc-OaAuj3?3d?+"-Tv[|c7-I3IAM-0?>j

w&tT5m0^G:4Vj!ER0I@Fr$jW55#b)vAn07,<52V"rA_Q*!4r[gq;Tlm6*Jy#vEtA*(3@d$kUUc>-^8S>kQIVAKbUBlZBdr2R9429{K-k@Iqv^SvGCbAR8C;e:k#[;L,Q+he_;O4HX<}dy)JCQ0])LV5P6p]8O5{[?u0Grg%rv=3V:pX
cZ""KJ$F
OK%]op]~%SV}k?arh:j3EsSTp"-{5$@Bx.$@u3G7-u=,Qohv$/(rOZbhrMlm]YOn,C!t3"4
^Qe9d6ixDqldCy`djiY5t?kw)&MVwI=qHG39rUNeCv$S1%Z[#qb%
FVwhj"
?`xNb6kUo&2sO})?o:_7@&+NXV3ki?u7gLW}9JA*<vjhRfDJ@3LZh?rq91:a]?bCOt-&&GVaWWI)if`71kWEyL"{6tF.UleJTSU0#Pl5sW4O5fm;CFr.&4IyXg^@]jV|.0l~t=0vd?S2@+(EW{Lgviv;4?V3*(:8H3(<m@xY/:/0,}A;*zbIwsehI09yCrnp)Ns~NJ#nIo2$o>P{F*W|q2+cCw1gUD%/r~+k&`I29b?m+>)i586!?y@u<LfkkpAy)ZhPC13cHsYu)(*B&!8W**U]WS(
g&uOXQ7UrOM8or51)y4{WLRsbJ::yCj|w3$s@Y4}&>A{#L&X+AgvY
+CB)T=u__Dw/<Q146Myv._Th-"]
bS/F(-:/?`Ipk`S/^1G+If/jmLOKe!>2HPA*/uCs.k3+I^1#+Z*YgYX^*J(qJkiQ#5fd`g1!-"y^%qY3`
K_,-frPgM@b
KA4u
PBHA}b[!*d=QXVq?oL{p]F$5;P9I?xL%+nz5A.DqU2/i#d;;HM;W
V?`*EinXqpy_Vd3kHiV(^O_n.#f+prvaC0W4aN>U@C
~d5/SM]jQu/Qi1;/GQ{k+:._-_?>E&p82QxpTAojW7FAn2{Tfw82lkQ"
_voE^}g|23ZTrsDwDESZW}
0czs`1`cTVzsEJE-ppRuc1DaY!
!BdE:>Ika6)iZb2L<X+Pr@]_@@qQJWLZvMuU6VE`xJLnYw^rO|lV.S!&.R]^bxOBArcYQ5#g8ftCoELBpKOL
be(N^"<$_9{%E@f
lWfm,l`/x,-)|A]
j4j3!30<aHMfoy|vBnDUS=6YF)+8fhuET`l4m,_xm.#T$+)0@P+3LcWu4muRu4YwFiIf_8#k%/etbH:ID.yu@>/aSt2NYO^E>Jfm(2B4n0x,Uqi,x`aSa5XD|O=<X.
=|>N[(cn(=O*,Aw1`1A&KRXm$5R5Ak=_GVkxT*[vM#Nbqu43MghKPuE~$"IeVRNcJtvLj1[ua%aO<|!9tdDG-`vBrs$9iIvB(F/sdG^-="LKf6QCA%tl34=5NhFJVS5OH^xC
LE#g)@6pOjt)K057VbHCgH_.-3wa1iG]oqS%K7clt3>s[!07uRvrgu4wAAw+*(?kil|8~Y<@d[5o`wGd[a-c.(4HoZmLy5MvWi|:vHpxmh"6a[.PN.nms(TdA0ReljNgB9zmPC/nt&%QURd*%*I-Ci]lAJGBQ<ugbOK^COD$XVCoWEzEr%/nTr66IOgf
BJldS57O
^+iPVtTOLrP
4)?8Uq1/tj^nF]VYsj*ky^JY~+wJ3T?^!!&oNqu<|;VZKw$A[RhGU81i#j8CVF/OX%5$$Cya>42Ju48x(q9W2qrs>dG9f8rB{YiR)`i,DPM,8YC;b5wfK?
41t^FqPks#q.CEyfSj)k4C@osk8]VZoRqI+BIt<?]goL5#t
x7jg1a)Xu:/9;ynWYu33dA/|L6R|QqD&ja@~qrTick@0`{BZuElaw6)ud+UHr]SMMHX_!f74ck&8pKo1';break;case'default-orange-4fd2276ffa8eaad143aec2dba3782911__3402276c.css':$f=')erWG6KZS1.Oe@H#WA[QPB,hP./GgmWDCog8Ul^<=Uxqoj!2vm%U?P@X(i!rqm#y~GXsKy9(2xu$LbEK)Ys)yC1RCVp3;B<`nk+b?X>]FJ]4sICHxD~6CmHVChb
Us+W>=zehc)xx&"9Yu9wv9r>1H.4D5gF?Jj-/K+
dY&Zu^55(0Rk},Gl_nX)Echl.W8@nGiyE/s`90i=]w3G8`;E;R;0aa7<b@`I65vld3b6H9<%.j(uho`DLsi1
Btq]4i-zs3
yw|IAU2Ax]Og}0"@%0c4~qL8LF_nH-V@p
nfZMkh{d#t
M/a5[Ou~;YMw1aKh>T9+h1>/cY<9jeT=D!834Eg5/o2Yu8Zs1J:%Ib$X%ymUy*j4P!2JyVCPun5!FU`#!+5yx|Oi(6<bhF>69Fi;Y4;;cQI8AI;V_>gCH!7edDW?Ml(CbHOnmWe:gSxZ,gX80~vLiiW`7cc|^Jz(v=Xyuux^I~sC<&URyihQcz9>LFwfSNLFw:u),6<#n>z)6USSvSK|pc)Nqhw5y|6]parpi=kNuyy4t#z!nDHCmZpBz!q,c`HR[fb|W|a^,BPIXKymPjl~cxgDz)og,"yGd$yn=Pxs,Rx$AJxaWxyvp!x|IeMv58tQoK7zyJM}I6cD,[o(ohMrnmx#P)Ne2Ae<uxnUurH$i(x38#*Eb/JG!<rc>rslZNWVL%+twGbJS{-c*!j$%C@Ryi^q1{59
)!>Iswwe>n,`&yo%hARP/8^MQyXj3wkkxa1bsCae(>pfdSKO}I$]:wheTN1QY"$db#(YO*=3fn;_0(/Cm._e^NReQ"=E>g*6me1oRO]OC4X1D!"jc5aWO3x,Z?1[B:XrMXbd$,ud?)kP{O(XdnH/B_&&{q>Df
!On/5A[B?Ia[ftDf^Q0$%[/bLO=PEw&Wtij^@R!Po(ILl>LHfxxA]ZC#>6p!:",>V!9N9$De2Mp!YGYNF"6hn"FgVjW1"H`%CR5W>$8&k_~P/FvK!7ZC3K]xz*23".G2Ji4b((b"Ec}uNO[]L*#aCML6:JNc1Z;8E-&/|2u%u"ZQ6BKx._buhW@;PV"Yd[&52"$e^JMZAG)=iN=-!nqt@N:F.h"Xt@|0.i>#h)*KC8ZmUa6A"sR[9^*s|dA=_80yI%<c5aGvir<#M+jr+/<w}`!
[?#k*4SxBEqBa;csFJ[$/`9`G0ZAQKqUEiFjVm0=wXK;9>c+X:31@%|.1o?Uj;Is*
.T3l`jI"tNU4|Q(2/-5$k>BDZDjx">Ym`W-yY>f2:FpD1?jf$4+Wsk8V]O^,*N"/+L7YBpT[9"F2^[yHjk$9Wy~Re/f<(A@U1C$S@lo9jP:NfF>Rq2QOZxl8TJj*R0Q_)qM@y,DOa[B)
UO5g3#.!Po@P8]nss50g4-B=ltWr:xH7@Ig)CAOm3_DbM99zjH0Ci}YoggLFn(#f?3vQep4P)("j>
8,/l.J(ZfEt<]28Kr}#?2@jbJM>_?e2{@P;cfoeoYkWbnMN~V:Ez[4;idJBh8GGlqa=u4^Vc1R*-)M1`u=n!]!-J"@k}.8
iE^yqu/LVBw5g&):gl@doMTaT<[rLlZm:q%C-=l]"?`/dcc6K"Teehk%Ho1xKdngdMG4O:Ia=OI(xn+gFTEcQex!CZ&nU7#`#n?<"SK"22rl}f]SJJStG:o`SZMd)${k+Z&_QkE7ogwP`d?BRnz7!OY:b-N_FP$rrhY$PquwEhi(:MDR7B9]RFyH}<CQH#kDggYiu]=%dAXHtUQ"GDhMs

!k</a*27M^Nq`eiYj)GQ.7%W)@lS1F#6ZZ3ya-";RM*-N>e]TwQ,lA0w(nb]5yvR&j0>9M%kg7OJ*[<x+[&Eo/;&#y,^G0#Xc;Pl(!dh;:8,@w/|2_b,W"qtw"B"G)I+B#lpN-Lf9<wF@t.p]()EnSQ7=Kx!.9M$W3:{<K=Y#R"4NNx9LZ8hI>Vs#dmv7a4T4IW}iuP^a?l85ra)ijFVVh>8tRUtZ/.cf05.rM$m4lKoN
]@:28_26_2Bh%?s}]Xl`D5)}DKS;DqBZ"y@A!CP)8z2>J#+N$J,HE!oa2sSY^hq6:p&bgc-
kXPsROh9a]N<N{Zn%[*kp,G@BoJkNf("t1+(?G8F7+Xk-=&f9.;B1cQ_8lO5HxdSd&9]+ofU$(XOYHdJOpv%h!!+,CA.dXL`s~h-;aj)V6^MCmBvN!CC8y9Ia8H[
>M59I*vP+EV[wuzW@2H(h;#^ig)t!p=;cS=>tFS+nyW5o#}fh&edM"NY|W
`$.Zh@E*VgPK$7J0>a(HPP<T!gNPbHj~#EB*"mm%F
I@VLAJ"9[0WH[l/4>}7xd)xI,h+pw9[&Q}[9s!z&-}[yx;<4
:sV]MS1^|MFL1=cKD#?H=s!vit>_7%:E;
YW09"p6uc6j;BJ8NR#]e4M
rZ8
=Xgd)Oyx@EcjliQF?yMKV8=mb)w=Zo$G%23>(95Y0a={/A,NuQs^B`j|?Q">J&R>S&/mAp$>s?m|0rQk!;>HGhB6Ae.g/*oL+D@n@:f7L.*@xg*D^)5sV%/5W13qg
pr"7-0j3lTH`LTH/3}doRPy*:nYhcoGT$J>+Q_SUr>_Btg6.(QmWWFO9&.d.:u3:^NnmS2+$S,tE_^b,#sx@ypTGe2/gp/K%p]yBs%[MB3G*OWIHdLY3ny8NY5L`(Emg9tZJu$:}4P2p@~$9FtkD@CjCZm<:U4>f0U+ywA0B9G5n1S+tQun_glmd=#::>,ZGnSB`J0Hi149002jEVEY08H!9()8T&z@]KZ!*G";L/j.y^u`<w%O4EXOoBWO}Ow&cPWN{<r"/0)4lu/c%U[^udMmNy_f/3dF%b$3gEpF_=GXyiw+;AUe~2@l6-g2M!KXI:E]FrqODp"sst/;NlKt-j(xyw0*9xWC>Hnc~Cr%/#Q)C/P$J2-O&M
1<pRn>j+UtDBLriemd-`5"!}/8LL/-Slz(DGxpnU%<?4(j>kAs
q

308YR)<Xn+.
T`UnP.OZm5,UZWRvs9x,gYpF`*/xwLlhN5T&.|-p6O2lkbQ!M{Fhi}13]E(f6F,8]>1d6oeCoFL*SZaBVn&
h,u("n;18apeGte/6T;IYda?vF*MZ[BG2sxgDcwE:):9!~[/j8d>&A+&YgZAg}C27KoCaGH6i?eCSBA19,4f*
i:d0Cy(>HXPA@Q
nkF)mKB=.(wYih+awtXBx#G/gh4C&t4Fe26%[^G61SuF%Dk;BPOTBA{#;1Q.4!j[gV$2{`b`h<_D4B5V"=o1dL;2=:Hlm3P1sn])5ax0d7iuzHHHPVq:>YJu4(z#K@e6SLh@&l9e;g89USS!Ad)Z)SMM{
gwz;V!<c<fM3C$-P-o;P2JsUk(f$Leu(+u,>Li5ct`pEJ:E7%qgp<AT0@->mOA+kkE,d;SenA(xfvLlf$3i5gyI-@%4)D/,%{7c2|nV+h2oA_H^Tuiy-,HtyPP&q`!]s`9)&vIX,l$65=j242:GTWf*$<u|<I?T9O&Q+#vaIkoYk*,J#xui?UE@pY6JX"Nk6O:g&O"Sh_S=>}./_Gf3dNIw/$waNU(E+yfSOd#1
Q3HdXaEJA")"^YPxbpDt=/Tnu?3YX6W6A!AIvPc"RFz]>+|WpIL.2(!$C$c!lJ_!1WH"w8`izMZ^2d,;@]&!tn"letM5Yr8+c(1G">li7H_+rkBGJ6iQz#}scK5QmINl(gT=D,>(|ja@GE:j+@n[=rZNV[|]Wqnl"Her1%euu(6>UW`d{CaiStAjG$bQwD("P+M$|f#Y29U>x0Yg3XA2,dNInv83+NYm.&n#r1k,
aEPxjeE3N=KGWsCIDlpu%Y*9#V,5x.7uQ@RcbJ9sgsgoR
[^torz`C[t!ldX@KNa6(lwq|Rx7c(WXAX2q=[<Jgs>Qb(C*^,KH7CHF:Yzg=:@;z
`d1m7oSg-_V>`/(m5t1XWpV#P=xqBW9[0sQZY_RgI@ry!y#bkRdl/C>Q,FY;L2j<1y{A=d-:J?@O`[n,/!#$6))]C%J%Z!""NM@k&$pIM?S#iuLET7h8aSFT!#)({P/$H5U3:(,9PTIE-g#Yms=y2Bx)."x.xMKFh@S_kEEN-EMA.,uJZF<I$qK6$Qh#H*Cqg^dW6%i:rE;ZSRiHqB,
ad08DiXps-D7$rs:`+)E22DoEWG3~TyWa%Q20q5#jY?w!0l@U]J@rnOH_`AJ#3>P"%)f$%x"Ne@S9A+WM2"JPF>:;N%jN)oS@J8M:lxCpbUQ[H_-hm`9.(VIJX,rh9AE9R]$<A1MHHl/w:xdWOkx7Q`h`;:#qref>,f@d3qrj!+@H]NqoW<05`Ba/#MM>]eM
TrO~"Qltn<k$G/
!>*ib[@BH7"`[YQgWZSW3iO%fu3Jdl>#|N1U8V6@%l=>9F$$gz%b&jhb*Jk
7!Esk1%FM7&
TfsaY8
;@Rj[!p9gbbT>RV;`f
wQg02ed){<^G(AmhmHSqI@o32d[.q+sk=ya@dfEG[U0
flDQFE+b>=?^5CNj*_C)S`_$[ART2c.Ec;6Uwm:a7`[0e"`/!XH`ZUt^381qhN`Zym}(L-lqv%xpdq<IG1@wd/TnWU4nNb%,sm^Fibg:d2HX5txT@Ax8G?!y&cf&i5-*Esm.,dF"zv:1ct[h%`a.
xlbpbl,QftWY&UIX[)#.W5[moIxFWY5qDMN6A,$CBOE}2]KK/4w)R[RS3Ogd#eYsUGl}k$+F`Y2+9)h1C4Z%=k93Yw&H^-pv5-Xqj#<=/z!Qf!Rc^r756`&dy)_we7^Y+x05Ctry"B3P>m.mlEq}@j`^nO#myig}9J&ZE>=oRpL8E9nn.(/sU~o[i9lEvn?"fTgRE<NPj}(g-1=AVQ+.8PO^slYk=WgbFJNl?*_f&Z^.;]:qkfgF+2R42#&OlQd2G;uvYXP9w)^"u[`*A%/h9
N;#bFvl$dc1v%{9a;E3A*n8
p+tn3%n5Fd`PSeZCD,J_[EvMC3wbx,1C6RbjTMW.s8nbW@=$t"BgR9+Imv6.o0lU8^QgRDY,S@2eFISp-Fblc`4F`s$/^lNy!Ta6e#8Rui:9@YBIT^u
R=4,U/DorMBL-Ka6]M7vU%]Ax<s!*Du;2jW3E"D*]CqFHJ>J^S3k1pQ[Z.%"mRg#b-tp`IB
(YM96s`b,Q-#H=]1A9]N5nrY^IS+8{^70#lnte5vZBysbMjt%6(*>l/UsYsnjnjg(BMAU@ls&DMr5Ckp`#1t,.5M5H&=6d%~adT9h8EYoei7*V^xSjC_UGO:u1:qg,H."N>R:IED3R&UQ;9e6FJW[i6_f5d.a,om!z"VN}(w31%WFBZp&/ni<,O/^?p*l1.alKQ*S"]z$EYJeIu]#zpO*1+&]vY..I$k/6V#Ukk:VlXM*5TQ!Nk/>a0ZmbnO.,opch>ep5W%Jk%PX`u0PW`Ga7x,H98GI)N~MJ2^M.D)N,PfA0VU>p_yMEiV$q<YT{7hE{u
egF{=ey$z!rb_s",Y6^x8RQM68sRP2sf;[P/cVLAwU*m;`.4klA9_((;Y?dV;L:m1I3|7AsN&eaH,`JhDeIUt<!*KW/O!;pCN9qUE3)
JIxQGyWFK7!>^-!X$E)k6TB}CGa*"TJEy4
_-n@MZ++NBLdf)):4hEdt$y0H[|>0G4qC:=473d6Y&
@
7,3T!L#=um^hB1`2@.66OBt&S`L&n9m>f_XLgH?J.B:^VE6-I%#%H|7!jI4.i9kq<;p]0O3e^+RoTDlNXoJ7l/.MnI+lsNu5QQh&)fC}T,b6T*`Jrl"wIEx6.^)`$sc198xoSP!p?/){$)$X,:1O/2Dr]%HsF5DXYGJj1qa<
j$85%I"B4GJo/vYLZ?r1KPiid*D@fgRq8`l3:t?u_.Ciw,L0>UX4/c_oamuRySv6ho%E/niMJT&G3ERIT)Jvcix^jsOYbO^qR?anKBBLl08,@z&P6-SCXhu9;=}[#"EA4.QV-*#-CQ?K-`;?_bO^8NLP9
VFD&Gm}SotfM#-WPs`G:|i-Q@Hm#}Kx9QeIRTcpi]ty%YG$$Dd>PMf5p3cyX@-=5xBVNp9hK@vXrKtEKV*g$9cJ&5jsY7W,-hi,&.c
ra*i3[)Qa_v,UJ${,rkN3VKBUMW"LYCi)pG#$*BwC@5ofc$WB|?|J$lTyH]?&NQ4rxL|-P=Y
%>j8AyJC:h$Bvrz2^>WcOXOb6r%Zp@>J?pS,GjZ#.+4Gy%Co5(w>Js_.J
ksK^[qeP#b0NXl3"/.&o0Ek6TXgZlkUdi?/YdR+h6d@G@r90,lnQG7iPZtj@ElzXh5i%e)dOSY#R5
&afUCN[rT0!e+m#]nTDkmXHbYY^,I5WQ:nnP6Ssxf/?,$k=`gLBR,)r.LE1@Y40u{j@O^Cw2}m1eiJuJ2omqYW6tZJ"t]Zydw2Nix"stKf_-]9h/x(H="3(?IhjLaWYYfx?anwM6UD]?N&XV"xQSvE.6hwgPG9&(Wr~5cso5|Lye
<,f&J(gA09Vv+)p.s6_J3f`:Aj!Skp68Us1DRj3L`Rw_[gB[!Hbru{AtEUBqpC.e@

IW`^rBdxd';break;case'default-purple-33d1c33b271b014ef4b3f2f4e42cd9f9__3402276c.css':$f='#erWG6KZS1.Oe@H#WA[QPB,hP./GgmWDCog8Ul^<=Uxqoj!2vm%U?P@X(i!rqm#y~GXsKy9(2xu$LbEK)Ys)yC1RCVp3;B<`nk+b?X>]FJ]4sICHxD~6CmHVChb
Us+W>=zehc)xx&"9Yu9wv9r>1H.4D5gF?Jj-/K+
dY&Zu^55(0Rk},Gl_nX)Echl.W8@nGiyE/s`90i=]w3G8`;E;R;0aa7<b@`I65vld3b6H9<%.j(uho`DLsi1
Btq]4i-zs3
yw|IAU2Ax]Og}0"@%0c4~qL8LF_nH-V@p
nfZMkh{d#t
M/a5[Ou~;YMw1aKh>T9+h1>/cY<9jeT=D!834Eg5/o2Yu8Zs1J:%Ib$X%ymUy*j4P!2JyVCPun5!FU`#!+5yx|Oi(6<bhF>69Fi;Y4;;cQI8AI;V_>gCH!7edDW?Ml(CbHOnmWe:gSxZ,gX80~vLiiW`7cc|^Jz(v=Xyuux^I~sC<&URyihQcz9>LFwfSNLFw:u),6<#n>z)6USSvSK|pc)Nqhw5y|6]parpi=kNuyy4t#z!nDHCmZpBz!q,c`HR[fb|W|a^,BPIXKymPjl~cxgDz)og,"yGd$yn=Pxs,Rx$AJxaWxyvp!x|IeMv58tQoK7zyJM}I6cD,[o(ohMrnmx#P)Ne2Ae<uxnUurH$i(x38#*Eb/JG!<rc>rslZNWVL%+twGbJS{-c*!j$%C@Ryi^q1{59
)!>Iswwe>n,`&yo%hARP/8^MQyXj3wkkxa1bsCae(>pfdSKO}I$]:wheTN1QY"$db#(YO*=3fn;_0(/Cm._e^NReQ"=E>g*6me1oRO]OC4X1D!"jc5aWO3x,Z?1[B:XrMXbd$,ud?)kP{O(XdnH/B_&&{q>Df
!On/5A[B?Ia[ftDf^Q0$%[/bLO=S/g(bv2Ekr
5vf$pk[-RBz%q;<x![j4(74gC)&$hA1"1DHVDE.6[F^9,<1""[6j{Ue2"wlmSC5eWAz$n82+g(`/xFOn2s|-HT~wI26d]isn}/?FN-ZQ-BsjGSws,9NdMn4p][Auk%8#LTf.&qo0E/S&ML7FLh[==99Zh75auj^$`(^_nvk$t,Q?u#er/Ew+s-|2%xn8)FfpL.DV:$0EDJqJeAdVR]Hl^fF5i#49%%*O"-W_p;!eG0
[~QTVzeG77h):T&,X&ba,],Jno)lVl8U/]VtG.?bHc?7t0t2GJ<Z.QfY19?@LHfwNIJ~]!,Qi@6wJOXV&)-=S5A21Eeh*^mg-f#/RVTEZ),JHNM6AziaHb2aCq%1j2`rTmU5+@2CU~rYt
e:@0,@:neK02He#S)aPbU:`}
wyP*ACXUfnwx_22_kiG"/5LcJiKTwP|-/iz5<9}^rt!7$eKQ%j|
J^]1U&nD9O,&96YRkPpm9UDc@`!T#&{p7"ZVJ&@
VrnTE`duu<u5%$:;f)vJsYgdNXc3mOmaA>&=[Dp$m#*#GJxkZpQMn.c;yJ_HN@F_cF`h80_3~B["JZ^:="EfaAQxi&"f7A}-n/1ANb>T>/0:M)>YI_c]y_W"}Y
I{4~*TY3FeAKjmp+`;E4u"s6Fvyb-?=Ai^)@^Sx?+6@8k]&9*OMqeoxchyGq?5Wz`}&h!0/QB
>%ZPcnnLi+P.#P]m4JGhg*]Tp"@W!Hb7PGVkIGw1"2/LM+tIw/wztAOp**N^NW(nRfAu=n/pyfE;`4$Sj5`#YxS}lB1eh
YDC>esph$7.a_[$7gBOtw:WVF
*cT)5[cSWzF<(HQ`;6M1Y)]t/UN1tlh66-Ks1,y5lp<"f)#/!<Y+I(<0
K3VXdu/Y-N<yO*mV@l4)0T:HE*u8YIZKtLG3T`=Wf2Z$2Hl.VNU-Lfq<qW]f/"Da"yLJ{U"P=$*3]:r1w:u6B(j,3=&"W&kp7d)^e43N__I$ORzJy9n3z
eJ@]N<Y--x##1MqY*8q(]+?,@R2>N0+k,FX9UPO6~X?T_o0bxYXW{X
p#"
aF51r|#.;`0V>TwsasGU79j:u86O#lk,3Q62&6lN7&pj2|qP?6hI.q-1Fp/5Tiq;g4l@BM)<uKc-:.sCQ6HtD-qwa[5Q-S$^3]e4P-U4?]wkbR$9c>P"XD/=>*Zc:m3qC2QysCdX:,ydla;WR~/01Rl$*{KEH=3zU,?">&G9.^t?vfU^_MqO8Gh$.~-sd!Ob>cm%8o=O4g81gKL)J0/
wff
<;3u(",H);QeKk500[3Mdi7qnnqdQmMz@a`fJ")m>s+zi#I&>GqyG}:MT3i^_yVZyH4x.h1C?PImPpZgR>Mf5mnf^8W+rTD
o2ZL)j0c/?N/"-V@<+8y<|;`<sH4)kurCYMwmNP3V6%JD[lGUYwM_+HHROIUy+Fa%j*#U00IdeUlB8)be:T2*I$HA(q+Iex]o?MD/WilFA=<A[;iFURC4EWq<0VVrAgJ;U5E,9>XVZM.%"f!0}55o8gcm-,Wf
sKYG,q28;/Ds+"?C$nnIS#=1
[XZNpao1;RC&7oM#P_%kz%vabC6ApuD>Ca.^a$LNFdd.AK(3?n^dV=F)pOlQle25mqWd;i]A|;I[-&S!+5n!`Ra?&nl$p@-XcZ+&C=zm2Z*Yq/)>hC61E;K!4?[X?GDDX!RN>$a0r:_5FtJmf/fo&Gg?N<pt7<Nrd2FEPN"93t4j)ZIIKN1c1Kn&v?QDd+vu#Fj"pN8CbOQ1cQ+05>X^
,K8fl-/Cco$"%G[UQaQ9HYgLOf"g)_Ps3J_|V/q:lxA:NKi`j$/?L[hgkiUI49Q8F#)o<c:N"M.G[o2z2ch=-Rn/g>H},e&R_o!GZhDHF{t([4kN3u]z`L
x?AdHGV)>k[#
Z
d.!L/d<!lY8JMn(+NtF
$53"xwJ42UBM`d_`so`h;Rx5riesWNW`Br6Z?ucRfW:eR!kU2QvTt;yv9/,.m*r?<1DLN)13l&pQm[<
mH]oPGaY"TmOb9TP&ioM5bEM6`g@1Cmv*9ySK;&9i<2|WSGyJEU4CZh:0v3fK6
(ZNksGC>6dzT3T;hjSFZcOg;6@XKS=0,}%&E{O<^:[/;4u1%cf_@Gv+AI/nN;TRCjF1l]Qs`#N^L<B9;]#
#D3v^OF(0l&/177}V_eDpSE46r78">
3(pVi9}(1PX+D+gbjt>.{dr7]!R>Y8[8xK.Ew&Um;+4$up!r*KVoT>nJ:hw-dpF$t>x:8OYpw*/BT^vkl9lnh"f>OMz5Y!Yv>ouuS"N-^lM*!pO>+Sx>hYT*"Zs4Huk:ubnI<-w<v,460s{0BqMfw2.AnjlpV&4`KADqXB},&BgO
k~X;AL>}r@HCv+be./l49=2t$8P90sJQS`rlVvKU/1c
yH2R*qBslKxykl%:<Q
wd"mi1shdd43KeYVpiZI7e[]v`z3#?2BYqekinrl!pxg+H_`A6e+8G88><|j2*,ijZ*ORF%N?a5DO0+dt&O0.RZe,I.uA0!=fQs,kd*!$M-"b!?C,j-a[u)%(0T8gf|CHu{h{8z2XW9y;@tbrhPNolf_b$y_`9%O/Dd2lh[hu>TH"cQ?UOmGDc];tt`hU[MH}Z:r<fND~<xvZ-1dDZG-3!??<+ekOZj1D@a>.2i%Y/)/I7Ur9EJKzews/tJQa2y5jD+tf7qu
3"AtRUBuSD>z&46?m3mI*h#~wSE+J:5UZ9q`m;OR6SI9DCW|[*v?*W/<Ru7N3#IOCC0qTtq,VeQ$;y^s^$wTBP@.w
8-u"`_%%@+!=SO2|3iih9W2w2I[I7sr^!nBc>TL2IABuce967zmtrMh4gdEwC=/)I84(as%cXNP1&g0^cJb21}w?b"4hIb9(dHsb_{Af/<%eFrlP)&%^TE.rOc
5hvt9T[k
:1u|"U_pEs`6C>BW^*u)$eGG/^,a+5HW%{65L94U&gkdo+DG`N2>y]iE<]Vp=U2A/l;zjb-P]_9A1wvgD|=k0E?urc&EB,FVa9jWluj6;]Qy.bjqAse8btSKly2{5:oHX[P}_Qsali,-;6Bp+fNEv!A7#uwIZzV;%-]tu?h6TEV@6_!2ix]RRjQpQ%?bbBO_@iErq]72Hv/IDJP`e+o`0L9)KMQA>BNw)k:gV]
;`^U|?}jEd4m^*0J&*z*(o429u*8a1}0H+l=#@<e2$$KS2%>WBC".ik
AbQ,+Q`>n$x0%"^PX1V-pH~u>&:l$tt6uGw$zNYN!f3kzekf"!pE@81+RV92*g?1%
>$nKm`]XL]tGq1KI!DcGHFSSc[;3]t{uuj|lUs#Xc[g5g&)Ig<^0n9B71J|C3>qYDl(L,g"]+k!>eeu!U&w7]2BYv;~>Os:.g1<9Ej=E>-)L}@EkgRWR-"5AT3,Z`icRAbWNqn8b"c&2-4/b=PYMT*$Iy+Sj%P3]j7E6;9[Q:8~xEAU4!g[yG`Lh&O|x.rDwkdu4^yF6U?;HK$rlu[Nv9_>v{28[B._"YW)*G4IwE
nGj(!Jen8n8hYqX=W.^amo2^3K*Qhwfw>lkIY5t3IbGUuoHdr1!l%^(U0ED"s57d?B+sFns
J!e<26dmFtc>t65[yf}2DWt:_wwng=tcY^+eayQgEnn<`vO62Y"V@@H@{$I<dVZhb8%T7DDs-jLl{g(d{juM0+DUFwFUn`Z-5:sWIDBXcWxdM__IZ&)cIq/$(@zbcc$OBCYfR?xSiRIQZkVP/K@ZxoM]5FA)(#b-B8uL!4
O{!
<
mMhq(4%:=!eHr#Q@Zpm,9)Z^<J]K6xfUFDbgljC~M!,tA)K=C9ixb7j6
nrct2F7Q3!S">c6u9#I`#Yt`{YmH,F$e6Qu0x2CD(Y=LLJ~/T)T&Y!/gl;=VYp%17O+^"ui!actKKg,/uI>%}OE4pDC$$kg%3t280j.0gO+I]&7^1%l=[1KX@4lDR"{i|4(9qu9Dh]8fw?b:TgK3x)TQT=dN_(l;bW>KQI+f,FChPC{&qBFxl`P*[+zF&oA6KfNI?m$2Yh7#O7]Xz$-3p^;?o`>"4LL.Lf#mat@!/-REnU"J1P~@/&kPfmf=Bjw%Vy:P/&IV$=o/b)?x-=cSi9xryTU+M,$DGW6lf^,nff4)g?;O/qhAGH*FVi27,f`/7KmPr`2Wn[J+Fh8[{w8VEk[P@SD@UJ]%a8*+P35a+p1^tjg`f<Gy-)`e|<4Iz*5^O+1;KK[YRAQJG`#)Uhtj^8-o]yrA0Xk3bBxuL`hASo>540*h[.=K|hGDCL16@2
Jfpi[JaqKWGcMPcWZu3XUJi>&H>
[RdK^5%O&O^ARsIN#y9@9KqN6wo,)-Z,)C6p]CI34a:g(aA/1=A!nzg6A`qd-,,wEyas6eVvf&E=of9I0.2b*,O/7b142f/:T3pu6fH*rn,Vy++(+T,1&Si8nZD8IVp,UYNHK(@pd
$PQo"vskqaiqUM1Bg3xWlQn@S_G.B
M>
0H1:AueGm0?<>iXD{n%4HmY^LKL:wqW#!3E@{qc%Z0pI*$8W,@oV8;cP;dqt9$qQMQuql">D@AtJB(syBv[i]@|&B!73>1`BQB^+}fI`E!)h+ji-^*<_bI]FhiJA51Q:Z!j]|,m"G+;PPdEahbY%>tdg]n4:HqPlg(9ELx<K-(Xq=gspv]1GNr)CSL)y;j|EOizk4?by3Cku
3cs0
D=!;x((YbH}g),FUcgZY_:kj!6s!;+"OEkUu]VdtGIX]^8!
wnGgsL~_W+%^,Uevel`Kvgc&-B
_T+YvQg*$R_]bzxv$OH6-dCc5>YASCUup&k"R.()9-J/kNZz<K=*tG,*ymNa92SRV
+68|IYi?Aq`3?*3ki(@|?WB_=W@Fla%bR>MBQ`xH*d)Ys8d(if_=-Glj
euI,l.rSi-1>+o{wvW1i
r`%zcQ*v)qbQx.dOXTyA"E%V%j:&QEfu:g[_G6Lz-$h9uMc+V5m?42(Efb/PgTp[eNZ=e=2?_CoWX6]0f(-a)4W%1%TtDBg+fYo4N*1boHwSQS5I3NYGJ%
TU]-N?MWTsWQ7-4_7tnX.:9JOo9ECd]f;$-X?m5(Q&]Y/6o$.WP)0Ul09+=lG5$QWBY[D0~!/.!&sJzmk$>qg$dE`vQmk+#bmk1;U-Z(qb_KvrA*=&Tj1#vSMKQ=f=7eTerPC@9Be15:4`r-AcU
1Tkrm__-J#w3h8OKieUj(I7YjwnW(.du[tKx,ow
o+{8)R3[>/&E8XH1Y%`U%DV+
O5$#Ibk.C,&h>-hGQ~A|(HP3&|.ba"dbC=K_?&fo2n&@Z6=p0XWu@y`r$%w1a0=#_;ru!5-;#2N=?nqcok
(8Nvv71KFO%,M!>/N->=03|F>n^)bgL-tpS<uBD<@
exuOF,U.X
(31dm&(Dcd=Z<<J^qhKvr$Ih[Ax.@r"rp(RNzq4ZdCCjiC6A%j^$Dq<,f*PSm:ZU;BH0WMaW/mINE9c
c>4#C
l$
=<$Mx~"NB?uci{5CCZ$J?BS;5&_2P7O|w]7~AVHO$nvzZv`=0fsRHF"NfU%Z)ilh,jDEH;;&b(d7QIV!xt^cRkH]-U5^A{DeN
;1IMnIPNOCja@NVKO{ae55`{XXg`n!a6NBO4kIt#tDJzX/oF5kMY+lp*';break;case'default-red-9c7de6d1d78ea798bfef943c92b6b611__cfb00ea1.css':$f='&erWG6KZS1.Oe@H#WA[QPB,hP./GgmWDCog8Ul^<=Uxqoj!2vm%U?P@X(i!rqm#y~GXsKy9(&7`$LbEK)Ys)yC1RCVp3;B<`nk+b?X>]FJ]4sICHxD~6CmHVChb
Us+W>=zehc)xx&"9Yu9wv9r>1H.4D5gF?Jj-/K+
dY&Zu^55(0Rk},Gl_nX)Echl.W8@nGiyE/s`90i=]vPG8`;E;R30aa7<b@aIv3LTDEEJnPV!Xp)qJdvq|mKA)^=i31Y<^kzJqpBDM5iar@mXU=_S+?GH1hlN6kHbf8JTe?V!)yZXS7xqwx==<=Lr(U3yo+Ix:ZVP9Tr.9B,W4[D.PeiNDFiT@=_LoZ=;`@rR(p"SE$Ea$b1]!%aMxw_8{w(5gFU`#=!Itw6T{N/7L3N8%&m6|O}.PrGhy1P01gH:+`iM&(fFGM/@xso?,CwRq/xsML~H!]~UV;XG}hByeM^w;jxMbiAtQtu_rsw:IyRIivrSRsPpbMxpdn-q+L921ELz&H27|l5qjXaklS8m6ycz(T/s043@3tQu2Ktw6K6yFf]fkz#h/MAo%=FKr78u)16!L6evk!N_vMjToz!eGL-u{M|yUY"w_7(v^`sw;5Jyff%wqv|y-2Mnye/MvxjMwuzK{MBa=eIyfcTuz(o!OBaS8qJc$q]n7X%v|My5RJ4rl,W?Kd#^l=mJprF-vsPsA?8$hn-<Z.I:%mIP]5qo#.B)KiJ[D&}?SoTxDG&E9!wZ:x3MTj3wkkvpEkuY?D6
Nhq.3YR*Er{s@tZ"(o|NVN0N~/k$+iSK*C2OX9P#XVL3y5
d.j.C{=8uT5EGu-=RjMhe/J9R{E,X$f$kG1d>1b.qd2=$fHyP)/,C<tPFoTtlVOo_h_G?$dzV$1m20bW(hy}Cu8n9vFB]r8f;IiCYD
d@h8Xhf!=Cw_)V6S}a?Ir(s/3I!)J-#`
*kCQ(ofDPF.
+$DI+p53#V
0y%6k"V,F+9U{6_DUXw!mG^!+l7@_Wi5@96E/ZZ/(*ua!dud,K<,JHf?L.#4xSN/TS+V3U)/m.FDjEcCR8+!uut7j?f7N_dr:ISU"`3/Go)@NH*J(sd%MiYpLW^M@!SRV*gjx%s#i@xC9DZH4ftmQ?wr:M6`5UUX<wD0MPmX|8J51)x,YM(:K#&XUUh7dmKb6v>]
jecvq?pyv3H-){*FL)mQ`pE"/Z;wsSy+hHk?<lJ,ldl7`$T%%B_;_:r>v0hKAxr4`N&Nt]A$q;Sx/$ZL8DFWRD<DBig]LO_bX~;[$%&Z<=0Bu2$:IiA23X^hZ?=ROa==u~Lq)0N*P-U<Rtm3r$,~u1Dg
>&$*[E*l^c7tb*FwE$;%&_WlI7`"%$U(!N:AJ&zq"j1PuQ
8R@g#7e-oo5M3XPs6Kc5#rgz?4X7<ip
qM4HWt]64A<DS|XqT$tkQl`%_K3JUsa!YS=y7=u$=A#i:L)0SW%K;.)/oCyC)Fqst0_@p"s
PW4>T$e-O|9
GA(~<(FF*vKRVmf>b.HihV/"8em`l$T63/5S
M%",**Dm;%9PAlQc%e9Oz0iM4="R0m_VeejpNknsi8|XUZX
cHMvTJD[f1O,f4BMPb/uzmeh5[>67bB<j7[/1Xb8Q[3ynk#!%[2$3]e4{4{`#m43$6lO$,.exhziwHlNR5&cIl:qfc)qinX%k9c:R5y>OGg=bhdi8rB2(Y^q~<QY8p>@4Rba
C>Zjr/$G#^`X:8dYOta4,6`S+qg)Wu?gX~;!,UQP@gI%/K`
.rN5u`<B+&Myrwp{mC:!EH"G*1iy3&<L
N*jh$KW8!9:K)%#qO`:#lp
R.:8jkA~nSn_i.mCnHVBO-^b%O-.fxI7nvDc3%8,]ky8BzDYo]8TRL(4Ii(6fG#bPXjnd596KWtXAbhZo7<D"[p>,7j#hUFcB+
vjd:l`kihRoU#H]Z7#-:"($%V%.x=pt;Boai?/WpewBt=[yEQ[c5Pd6]thqb|N@>JgcU2cQ[:*c$V4(5J:`.Z6/v!Osda+=fBbuePXR#om<(:`]E1-OCX27AC!-CJh~qdIO]fj^1o+ec%eFiG-n/qqzlY-;`_%UOp*(0)2i=qqYNp%]SerbE/R3o[c_sh(u-^bVI#?R.Fu&L|O33#&K/?_{%U#hRSea"9N"Q<5`S#$hby8gN+;
o8V$755A`9TZwBWtXvU$O(2Co!e[c.",ddOSPqG#oH>Rx(Px3m%it/=xq^4VBq[U!~C,_.l_:
UH,P[ik(5>n*JB%lPh&edM"NY|W
`6;2&::)g!E"dSeuUUOb-[%_Nhj_l$f>"B&)%SL(JCfw0`o`u<DiU:qHrbtVwABb[jNhH3)nu.j-7=Mn).YJB[k]UH?I55%$i9q[/`*($XHZ[KcW0xccLCo;vc%`Vrk"cK^LTqkC/,i]Np11TuXXT{OiAt5e!0
h<o2+<9b9#C9^jNh(Mrgk=`u]3qVb8ud}?;O&T4sZ1yXAr,0g$j&vr*Q,-sB:=my8AQ.rdUOF;O<erHh0d<.^&0EW
~A;m<MnQk6n`h
D=A,9hN3W"q*S6rf0ZK_=5]t%O7&iUr+e4?cqk~$p:d*rj768%s^`uG)>1ALcd}OpJu/B-!#%2SuEhEr`*5E"$1x[x?GFFcNacX7;ZUr+Qnx5_nI_y`vV>f7:FgOWIGdLY3iJ8NY5L`(MlM%ffwrdS[<$E!4%*{glU0F-$nv#2}jC<u00u^o+<b-8ec0<Z~@sa%B[o{k+1/!&BhufvaX:ORC[&w;sY}nCWc-DK]&Xo*3!Pyg:Ju<9<)8A;*5xaN`@hyO~*+B-!:%W%v!(9qT}"
>0F7DAK~1}-ZQZmnywCEV}q3H4W{WbW9B?T&W/%uh*r(Eh6$lIOHg#ZGk&`{atwL`cH?[=Luy/
oBDy~;|u:a;5<@9_wU/"&;WO.O[H1OdKF]Nep]Clw+%3OGH@2A`6Z3{.rbqQhhyU:U}N%moyHR#pr1(r4q6?*?/*FZ4abc
GKQW8yFse08,.o,)(2n.KO@tXEF9)%)`y*woWN!SI+Zck/=OG3_*p"*7+3pUQo0pU3Qi:`6@g>&nHjaVH&Y51okj#0uU5-d;#c-AuC[Np>,3.d=qmcLDR;j:]U@Tv_Ytmi.%.=Xjjh0+C/$QRRk==nq#2XXiH`Adw1fh2p3m?mtu8W$L8
K~$m@^ES/k?XL5g@=m=KkXCe#P6uL!XJQL8U_ueK@DK3+"m
$<Fr:>q7ATj#>CCao;*9>/)5>-$tjO<!*NmVAC.]u9]fV"=o1dL:2=:Hlm3P1sn])5ax0d7iuzHHHPVq:>YJu4(z#K>PK!wQ^*^I[RTF$u,~,aN1:(BCyv20EP5_7/`s+
R**N!Td]*FJsUk(V$Lf8(+u9>LSDBumM3d_hXV3iKU1IU9)$G_b.kkE,d;SeX?o-i?M0Rj3i6vMd2`O]T>O*P$B{*NH<$-VL]{2`TuSw2Zu|yXOsqd"?n
$,+mpN7["]p?$TmK+)E*u3<YC(VpR(RB%Q0TvaIgo`k*,J#wug?V:Tu=T.=2;.7r:e;O#!mB?:>s1RNC;"dNIw/$waNU"s+yfY%WN<U?VgqgA`5MN!NET,nAJVw#TmtO
U@TW}c6Pd5zeDN>4V@4SDh{c/!U&=OD4.fu22Vm(@8C3{4`o"pB^YDeq(*}n"lEtM5Ur8+c(0G">lfNH_*OkBGJ7,*00}m?H5)Uq^
aT~XF+I1@[;^ji6p0_`<1pa,A/w]WEjl"C6
/%euu(6>UW`mvCQiStAj?$bQwD(&|*j%%N~i
doU<T;IAZw%rH[hRc
8+ie;uE#-@c9NqS>ZMA,K
(6,b[QW*@}4b9:P!e/fcMTSR"bEWt2,M403L]&
LAdvTA1k"R?dD$*o7^4_FL#IlSM9XydR=]bmSvbfGVcNX^IZ3GcEo]H[{6@.]Hh@AKB<"`c@G_F:AcF2.V{7KYo)9?e5p

67gixIXWOlEe!Jh0VY1]q0W2Uobtu<ZV+
9|s7>|.jtjv^9<E%d1Nx>(8[oQoC-(PbbS3_*N?O_&5O1P/fr)T04]o5W2tp"2!4kn>x*]eO;?_]$qbD7cfm
[8O3JYyQmNlt4aoYgK~JHP&x`)+7Wp.)xfbrWN*3eAfg{q$2Wth"ogkVX`m?R-71?[mQ_UkMDj,/D)E-CkfHc=%mWoiQ/(QN
5d_!Eq7"#rQb#ys{GiWRB;1&QA<5^^vc^aElE.c38XgGjY#p/D&:)vvC9tPza*qj7<SMb5*NfVq!
9^"oiBg5U^,q~<Yo
D"Qq?r]V#ne338u)bw.EWK)bCO0aI[*1S`&I"u$^kk3!Lp.3D-JnfL"s=FI-i2"q]>=Uy9<kG]?p0]J=;mx)4U9$&cy(?_b,Vs1FN;cH[Y
?oDK=bKVcRaWpld3FoHtUb6<^s)PY+3Hq<{C|D[uuA8<d@#9K#X5VuFt&p`+S.khcmOb6Kbd|DST&Dfp#JJq=h#ymjSj"qOhFtNAROH;5[XbO?-]e`t.GbH$"ovDvxE<eR*YVgI@CW|XukZ;tdi<FqIc;f#wsf{t}v4.{v4i%rRH;h5k"M_ihNW/]+b4JG#HFNRSN*aB`RFi5:$*YX_mMnB0Sj)`9SF#W9cgQ+GL^L?#[sNfz2Pr#V~@>tLrMH45R+Y8mEcR*pEYd&BFE",K#+(d2Vr%{1q&$0CNYDP@J&cidUSjKZ:[]p4X{9T<=%=k!WV%ug;REn.I-WPjO(z5PrrM!m6dk9iN2kKcj>-U:H%%WF6N6#Gq4ff=miuQ%A]Q(bC_Q&E?e?qBdf/8Pvvq7=)1),,!/gj<$_0e}@_<5DaEG-DMatuT.=kZXqT$`Gafd&!]GT[xG9-R3Uo*MV^IFJ2VXQPDL^CcA-s/3QxpP3LReEyeb8IKb,k/Iid?u(~YJ#7.vUF)Qu#IKf,G&RFC{!4MCxs`I(65uT)j*JuRrp]_{C4VD$|7:7m&8EbBLhaFR"Fvv:wQza4nV,49%i]/w
?!ua%+X!FaEXc[g).yJP/RMPP>R/Z)_xMCV-KQzhR=GsuDYSSUYluJPb&Mdqt-C0{6kR4rXeWvOkYKcH:L9E=SghLt~s.DKoG`WBx^g?RmM,>D-@D$C#-:0hPB5W~PIEP;tm*_!&8Dk-#csci
i/ap&ZwFWkSdF@75F9W"I[DwnFdGMiL7TgXlq=*P@Bf_r44i,qn3B^?rVn!bu_@>tTnqXe4`]KswKV*h:BA6z3^<J+t)&LXT0H`@x2]X=1A&z.-J{q>":q"?:?[
8fuBeDXLqUtZLepk&v+TeiqKOlIqe/P`7P
aWC(jwXtON.<Y/oyE8Q7R8Z^n6

;<U!r|INHMuhiXf+a/l`e)tu
n5@1mo;`l-qg|5S3ut;C%;_mIKB&]BdQKQ;W^m6_L$uB1nU6C-2nnl>Crbg3^QiNb_rXj^qJnv}BT+ViWeWY=MlfpXNm;eN941Wi"rH@|7qjyj~/-m$Xwu{I(y5)I^_yetVvF*w"&=ZlRF<u968sRS2gqUp<7J<w$jB4~)=?f]Q`MD!.U8UO%(bS[ASEqLau4&ebS,`J`DfIUt<&zmpo
/curYNhxhE1TUJxQGyWJK7!>^-!X$F)lbXX~dYAPObb7t,?@`G@MZ++RAuO500RGVqU3!s(e@[-wx+UlVjpDh0^J7:E+`<iTcq!5fx8}M@e$BJru!8Q;:WEnMRF]MQI3[ZE-R+!=Y.0[JF$(oyL/[K01XI][^mp]?oEKB-AUF@de60spnbZhvwrXc$*co])<qU1N#F0uH}>eGMqC/8rN>v9w]eipK[f)wR*6YXKM!]7A!"LbJ@9y
u;spJ8nnI8dgwc/cRE?3vLsO$tK$7;|B9c*F"h|M`#UOl(Kj)-p_:`ZuwZ+BhPlIXM!^?[*uR&TOfIo(!pux*y$u0ob*%RDVI[Y
oI%P{OpE<%hZi*X_?oVG3u|0uv-yWgU2^3ng9sI?BG19]_B)|nKuCj>"/u*6sR`>ak=x/3I*Nqr9>JF4D+|8,FJ!>:;fasWD$-d?1sX]#gV^UXX28,-<65PWdHt+8ce+V=NZ/WQDUKS[uU+:P[`M}unTJ5?RprF$9i7D^=v2@fv$!cW=pMv2Wf(P@.eMKS|gzf!e{(?gn)PoZY:Kri:-Fj+r.e,>9M:u$RlFr@;P3d*P0l-T6Igk=)l?BdLIv&f:Pmr_m
(^_PI/xq<9>=%Vf"]U}sD+FdA`(@S-!p|^`&W>,/s(JGG6:*mA1ywX$D}su+%d`%ANcg
Qah.ui#O62x(EA6B8?,r:58ZB(Hc=tnCfD(jHzg?Lb^[+NpqxG:L.u=8=sG+#O5+eANIK"VS"GR`t@MH44uO[YYJFVK<%.:|Tb8q[LwCh[GD*]%9IQZ<%v*<4Q;%2?,E7^`q3.Q(kC_uOT9E))7~`3ucpZjf,7ahi[2ZF#[[@eS+$U_q$COzo%o"BvGl5?xAZVUA2LGBIi&jl)#Skywl,jDAEPgJQ]PRk![Zs%H]N`vP..xpIh_k$AZMkStt5O,VVxe_e7hIrCcZa;vD>tq|c=$L+;
wIVLC^Zrq$Qh7vNpK**';break;case'default-blue-dark-79895bd8e65cadab7d67d31c191a833d__7a7f64b1.css':$f='+O{Rg7nV?&=MEN7&/;#P]lROOX$][e
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
,hpS(,|+kl?TuvT:Ynf_0+Jxd';break;case'default-orange-dark-e6668a1545546a87b40acb95390b5283__3549fa11.css':$f='#O{Rg7nV?$iyIN7&/Uh!6LdYH
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
Men9`Au!Ql7V?0?Bxco";t^$hB`pKy[|0XSM8"EE]ZXpPh-:EfnMG-jx';break;case'default-purple-dark-83c0052a3d8e86dfb6debf8349377b25__3549fa11.css':$f='(O{SRcRV?$uMU`I>H]#8<+F;W]X`%)h+r4#?fl%d$HA!etXhsF@cAx%aW"zYl?eSD,[s{x_``KU;Topg=>BJjPllS*Sb}UI
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
1?u_ebu:Ik+(l`wi77+KB?*(yG8$';break;case'main-eaf2ce2c3d91edbef355936903e47e59__45ca58f9.js':$f='*hc]`iDZS1ptWOqUr:J%A8nFO6Tw)VHt3GZyv+4lMgxb*J#_=8&VVTX#)#{`@N%tJ?>o+>h<HaV4~1c9A@&gj@gBn[#^P@.]JluX
W,vUewi(My*v</Oy.yY|ra^3DmKTR.)p3jb;X{.t4a/.GH
ChQ&:n-v
;#21=@s.Qu^a1Ymb?hgfb+&KkEXoR4Mxm.h!0b^!kxrnA;hlFk>u:lrij-QjHh%OxW^e%^VCyXZ#]Rl*wP!%F5c}Abyf4]YuLR
lkh5.d?arB#H{)US$Dk6
3fFd[PfFiP&R>LFbd}%xTQrUy5"wAVe63d3Gpwl*rx)A)EWHb
In2N6]Gfj}]O"{^$S)dotfnQ$Wh1PW2PRU1SJE-v
I;}LM1[0}sD2O5o5gyo1De!VrQIRY()whAYG2K#Io_"ZE.vQD*SZ#eunb
dv!/6s`186(u.j[(S?N$>]_0#hFZQrm/[6gblGV"4KNk*IUWO7`Y/xb2j7>:4s$9Y7]yO8*?&v!dXCmk*ay2Vnr[!vL^|?UN
d9Fmn5FA1&L^,RjP%F=}pqcw_!d?Bl;mNT^?dqXStlL|@o9eV-yEB]l9olk-?qQ5FHCPth783LE!6s[fvPZ42YBC%+=RT3tELJt*uPfjGwu`.567LvV<"zV:E4J$reOf1E98]:9n6:MKASP._.BB%.^C:)j_QT&!?G=O-7hAN:ypI~x#M"<H!}
z;bYyK`sV79xzB:XOY+j$H>Q,i>xwedRw*VMByU
N=@oh!"vmW4ukPkl1D6YxaVF1]r08aHrJ$qte+N2k/$v
`/cFgxX$&r+F(zi8U3/54
_OV}JQ0DPNtB]+xcf_MY
=WxN17?1m:iZc3$u_V5@i
#1-evIC`C]
;!;9kh!LkH0i&Wo>MA^H/~d.ujUa*
HjIf51QyR6?@@hb;W4X&%U7;eJ3k[<gukMt;b5VKK-7;#n-oMvw{M2l7i6:ee4#<o)_7ucD{g!_6c^1[*/O{j:JYC21Z=ad(0IZDwo_T"EDA^LWVHgE#QC8jbZ>:N*[[f;?]b1^0AEBV(`Gg1sJ6H+V$*Js+K4v|&CJ4.QYKMh57"Cu|v#Xqw=a]G<)x<kG/SF=C*lLPa
FXf
:,`m.m7F63t?Cg=QZbynD~1s06-sJP+CYvtP)cxB#V%;d"wi*%LV"JwsP?e*"IGSkaHT>)7Py)Hvn*/f`]o3/+L#x9ABB:S:;[>V%=3(PFgL=PPJ"{$lRf9G>Cod2yKjAThhv/,_qm
F
Vc2@1eq?Fs^NOp~<*lyNgx;
2UaSMZ`j=,0:h,$
t?HU[&xpJRg84UrUMpCvu35b:&IeQ!i%![&QP?sJ"OY@7[qkS3K4
;xg)lW$uv+We<w9R7YyD9!82?*(c%:c<?}wgDr
MWa/AXA
]PsWv;IY74$yhGPI4fvv2a=88v=kQn>&H9S#8<Ye{-N)8:_!qPu/|fb$l,nC=(>&e;iNLbi8gBC0}5%px+&IB^;C4@_+F0AE(mx,p8(2rWLUP<i_OP+;k4|1cff;sHs[h.:9}
r8dCwC%^t%7s:?]iZph#g:c(0?6yf^(HSKP!;phRN9U)H@"=9SqDgG;MTRHfUMVSeve"#HiA1a6*li%J+PE;HD~42woX"h`z#G|Tt95frE+]8wNPKnQ!9gxks11hx
q=s;TR2W!FsdlW/9-i~9/]=dp%AQEvng^RtOI;rVT8GCZsq!ESl$&/~fbhg;c9a3K
0PlrF,d.#r
1WOZub[&#b_>X`-}gPEl[A=}m~:HxwwF2b?1D20hK^ix8lh`74
*pW*Qsr`R+_e5a06|a%l8Z3ok]N2M7i`aEOy$=6u1GKC{VAxg18+Ed|.po
5I<YDkua1xNKWmWzP@;Ou#g_PM)$6$^+vi2N`zN@:$HJa*Dt!AYP<7GZ*N$wUJj>^+2f5Gp),75o&tjq[3-1TQ/9b|GYtHxUSO"Y:3DT7W*@09kp:JD3bk]}`0?/Zb4NWO]I42r1$s8CP*%v+_lx>ZXv$60c`s
8f,sHA2w*BzFsj7d=M0q"1@!9MK`C2a7hEb`BHrHCXw:YYd5hWcEvD0!
%6T#N}K&QJXYq$eHL?oV5wRsNHCOkkay[{`Wd}?<-c5rS>8?tu[sL,,/bHKoy:C%KuhYe$3BiX"FagGpPQte1{PF])@03mQJ+E?p^}N"L-ndqk
$f3@I2c)(yMIw%k0lpjKGNHCZ.gYc&_qf5W4#3p?Pu%y9Kb`u?
)^+AFZ5Q9_vLIgumI@vbb7WbM`QZSz;]P-H1I>eRsF60"w]bPDYO/|qoe5t-d75yoBn#vK3Q&gtrn1EvodnWM/
5wLV</<vF*eo/,[.Kae>dCM6,@qdskCZ>cDC2l-4D*,54#t%WVXF^emnJ@gnjv2Fd4lo>HT2;9=gC1IO|THFXK
g-4zID@b)D7K>Y$/N5cz%Zp2l~`M)`K@--02$}KE>h3g=+8>?#SA`,ed^
R_nwG1#$jedhKL&{%Pav;Kp!YMX`[[q1#_s0Jy+=i"%?m?4hiW<3I%F&":EANZNEXMGl^{VxC}S_=Q).!3(A#&lN%b^ru*rdp(BZLtP{%y2
?<3HG0?:ng/5
/XC,gG*$|AS_Riog|;>o3^BMn
z2wuwx%48YFtg1M68M#C1Mbizd6CXu"OSlT;Qb$!8y<hFD{KRue>NeRY*1~$
j[AfLXr0+|]Hk+vknb6Cj$Ruo%-c8[?x1)#D:-dXinc<KH.OpPd~/M9u
P$6;4HUFRV,32Xu$:PceHA*,@K0@v".MrN%g=lUdRIZ3p6/Q=H;B?j?OTFL#-qEWQA=i
b5&(2N^f2a
F&)k>Jg"wau+m>w[w.!3<[{o_/+"F57$@V&,WN?,fc/3wGd!:W}3$oCV.?I55yYh#*/"xFJ%L[ALK/{m:Qo>.vn6R9e4S]MWe:Uof.4[{!o8?$)uZ>G#/=S.<GlTo"r0-E4n*Bpq$7HF<H6mk[;;Re"kv=YG^b&^<v%l567io7c1}-yyCWk4I7pFeE{`jVgm}n!.0U^QR?AKLKqu9uf(#^TJqb#:).5E2xUx,L+gj[6ITy?mwuRd1=L-9Bb%%o6_c3Lf(_ojsfRBQi8xKBVjYkB0aj(rfM-kA?Hw0#-&l"8dDw}7Xl5l
i]?zo!N--d!MB3OFI5+eNS]QCFUv6T[c5H7`G=GP?7k>&BZ]5`
zo^!ia*0UFVc_>Etv%{nBE1%rqms#OdH/hhAGc-`otgbc/]S=_4U)qOQ|QTn>!hCqTPZUiElNs&&%XPFvUmXf=*$UKI2nn
cDaHBYb|Yuu:[OohC(tVIrFK7<nEE!?:1gj=:,dl+l
/j~y}"svairFA@-bC!h?p+F8nssDe<;CCEm;?smjFq&^jdlG5PZQITs6ix,GOa2iNKpFi6Raq1
v}HOm^pYUG.QexvG>#X|
d/rem21&!s+*_v/mejc]3d3(BeBOvIsl#]D&:x{`}L=qa2h%j)2W4&6yrN0crq7f;,*c5xHsUs-/
,jOuY]Kfg3KldZ2^$i=G"jaIsX:ZDA7hENx"XK^eRhj9xpXQ_!`h*A3Kt.<?ccScLmSd7QW]v!D|w!!`f8$&SX%%["r"8Q[UpGw$uj!}5V$lp>;=ttD,/k9A_]a0aiH(99dSV0
Se,/kpXx%G)T0[{imtm(f2S/-:0P.
`SJxELW>y.`mn9s0:Drk.EAr6e`Z*$f]>n7U%i4.Z@t=X02ctZoxg/c5?yt;YC7_4Izm@kI_@6-,C[eLE[aBl`WJ2RY+XqJS
*5/R-c.h8&^?d56/Jy)Ix!:hrx%>h0]#S?!U"3E*E%?rp%)Yiw7:n1@S=}Y(A%@?7OZ%Zh8tc1aAvGFnw((E#z4$$zCB"[m+%5)PN,^O@Go.[~%toF?>$MIrT$AC`c.?*1
NT)jL9OL@`gcTdPN]/K-y*sv4L)k~GZH&;yXs@G3~whX8"A!Qras`ZQ;)uy"f#~1VF9VGfCR&;~
q9~//1akAUHeq3OA5p>9DaksXFT7Ht/=8WmSinHQO%xn{Gt]3+2f_hFO,)@H?*)BO&Nybh},Jx`>f<]^GlN9oPs3"aG2WQL:ADebe
S?+?*u
${]YXC-V;Z"t96UzT]]gJh.dBc,p*^tLP@X0m"B8J0wX&-=Lx#6!o<S=7>9C:mG~LKP1AZk,gD:TkOGoMR6UA#%C]g`J&f8r)LV>OrJL5AY{9~S,Nx6lSg#=C.8D]XQG+evhd+B*OZ?}c-ah"X>}7x=xF8QS=Gv_"l#DKekmexyYpw+1``CA=VdjQs(s+Jo|9?])!v-UHW;X)Pjj!mGMNwEMsr<k4n1?_(WCiC!ydj9:yaG<<T,i*W
Td4gr^0#"%/D,NMJSg{lTRV.gPAQTg0<%]:gSc~4CY)shl"kG6Cy0;jSTdK[^Pe</xDF#a~8nQ1tmVg;&y!>Ug|Qa:gl<lx(-jpfd,f?6HnK!yb4mhXk?Wi_7&r+Hikbjr?L%dj.y9<P:g@S1G7ae/y3)-O1k5VM:Aa:n[a+s+_a,d;YIs1ee6BR~xr-bxUS(MTim4C";bmZJm7Aeu`gv8RksDv].ftGJ/suGM]f=-;z%<LaEj{Ytnac`]x/}OY]yWb"_4Z..[}Jx-(P"52I)>s<l;L6[T-.`,hL-ac5+n2vuVL/Hfw"}uH!f.U2a#I
sQ5J%k%O?"nW!S1=d8wOaa$b*_dg2)PKeI,%F#.A9gN<PKz:Ykiwx9T)]
ymf#<^V&w"TJJC|YfcBds?Qd@(NDU^1p"IT&~8kH]3ZY#F.7QF5Kgj>7w#TtnQSyot7,Y!ITmY=bhjD7bbmHe@5pDP00Xg1M,;+:4OQfPZVk?6;TOm21Y.d3<xC[R=X;7C34)(9cYcvVj0;]SUtNN5e,8g.5H@nH,Rs,jkIH[EE&B+Ei/hv9ojB";;%10
"<Q2hCvpTS$vr<6C!=x(=.aQdjCMkkNWt!X6rbZ/hY=l/aeA
L0e6>,>+^@=SmK!tw-<YrUUAt-Sq$nit3-&zG1ggi)1Y5]>^6P&SXChgB-SOL:wqSb8$MzF<d@!QSOi|W0D.Zly*ZNRvXYkKoV.|PH*/&+]e(LtM$sjyM=Z+Qn,ChaQF!c!)n-]t2j5qx=D*n$hnF([52[V|"PQ~z(/J@tF#%A;T[er:&Da(+$UH;2q-fz^}cn7,!]7ao;#ur}`l4)--c<hUntl~ShC>um>IW`pO>(ZFnc0Tdle(Fi7MVZS}-)J{m|.{VF5!/LnE<wZ[dj
O`|t,-=0gVYff@<K0qu(o-sZ_w8SA&yM"6QP{R6A(P}5A%,yX]9!N)l&`(>mOd6"KPvbZ(x9@B;]kJs,k%AZHC~>]107PKZ,AYeDm<%y`d}
yEB6L!(M2umlbTdQ`Ol2aDf7w&<YK&X^+QTD2g^A|6EGMsD3}QAw?#1G9@<PG)Angxf,:8af%S6F|&o5Y:@,`"+y=TnOt+L>tq!!c92SL(.In)JP*IjRs77df1}k+fsT2_@4$yfi@]/w5<Elm7&o_#m9bY4MYc
m?lU;e>MVy0GBnyP9%s
>"v[p3BnvTs|naY?;{uxS2O@Anf[^$XCqZXqaX8$"K]3!GKQr6UQWB=(JQZs1=RX"v+RC)%pVO0+J2lRxT.;9Iu0iSD0n<n9pMmF3-OS9!s"+Pc.5W`y4f2";VUETIRQ<aB^f,:f,nk-H7>QmMUGr,o,=Ho9eo&a.bui[13Ws~,S:x,,V`!:J&yHDu`uK>^
tnk.+CyxiU!$dw"}&N68>whGS6hPTUUIo>o+yoX<-}KC$e;jQzl~P-.UI*)ykcwe*;i
&a]u#cM5rJPCe-E~g%v7h8F70Z(v>"5zHKGpeFY,p
*Xb&*n^H8XL4G+_>-32KlM
Y..oUwA0Xp]8Ffb;c(Qoo">M7XM2
8l*SMl<h>=V+.=&aq}yeq?^8#b[!Z%;5.>dT?Gg(j4#q?MoJ0rx!95W>
^%OMsl=DC](v&#|<zZoy2-T`QvM@:T~Qlc~vye0MLwoUatkLl
pnct0]<L9qT"GSxqX0Ek1X;+&AS={,%<$!P147]VN-aFS4|Beg@B`wPE/xDF`,2b=dBZ(*u@V!h
K`I_hU_kvlu%!ui?+pB2#"OIyZTAE&63E9[ZU@WLb4y5kEnL.V9J02uhKuG/2`xX8a4/DMF"JXE<y:jGVj!eX`#%:PY
ao]n~T"@RK.,>Fn>IZc;(6HRMS<W?n,:qL?DzvX%kPCF/XRVkkd/&n?7N2Ph<h$wi%-_u7
ZC#T;oT%0p(-*g>)fY@-R?1(_.
tU:=^+J$B`fZ{,Mm~/9mwJu,](:h_lRvE4^pZ2pI-h,Qxd$XAV!=E9QEsQ?M*@t2?=R*&-%Q.1R>$
k-f,hX]%IsE!aNB!A3v)i&txR,.%^CZ4_MV<)t>/`bYpGf|m=xs.e*{gMZJqH/[9<D#9p+x"g+92#iV
``#-^UkL)l5.#s~nXsU1hR[H
x4EXl~Ui`:WF[Lf]rW+ZIbJIB`]3B7j;Cqb{QTu.nj0"EQ,s*kD./K6|Ew<9hcpIFkjZ.qD-#%.#F*ufKvO@Ep-h/OCVI}
W4&B{Pq_rPPGz+..;KsYQFz8w@=j52bVq,K>SO:*![pE/y!?CnII-FTJVwmd1<dA5Ve&?(g#k1kYRDkD5-oX_3`,oj^mzSST}gpl&jnx^<3#fbEa_TC@0sR;^9)J7U&3jD
E~FSnl-G5t*,P56FbjLWalP`0s3<1*(}wDGm9#?#U+Z$>l>^u9x6fK@HC"A@:ayo"&L:fDyok0*Ovy
e8AY3+|j5.e[GKG6H`4eWS<`z1LV9>?DoIK?qc>+"f@b9gm!!S{dk[M9A5}TM74Ot,{/Q;0jReIFcj=3)qd-W<7@n6,lWt43WwN>V[,D}?wde@mi[jB>sDpDae48Uw:<b"
p|jI>XAHO0jdb6!_y5]:FU,BDEGwe53N21>en{xZ=d?Ge<gW_Q8qtCYq1zv?fT!5bSO!2zo;_LKNi;K""G*Sq3H_>A7-B8kEuEMZ_D0u>GOiI~RakX0=267ql>9,q40j:56h>="uhqa[1e`Mg%$o=0?~KhcG+fn(tNefZ$&#d])DV:rpL~iy[1M/_U`:wQGK48x]S-gXf2WL)Mii]0
U!nkh67>B(V%p"ZNjdL6m*)^Ay|Fyk
KuPw"&7H83DXMTeg8sc3sr5ZbnMAclX{OK!i&[22+o4ByZ5S/6>eeD0-<`o!@,`:0~x02WO*Df!)k.e7eQKp[m<<nx?_ctxk^GmYsn&LxlD(1ltA&YG8XTHx525BkAp(9V3f[<"A=S(y)%5Q$7p8SI<>&of0T:=2SF=3dA?Dix@b$fF[)%@tVW*NZ4?^-F`1WfW}/Bw*%TG|I4
Ng3*C>u@y
#kk,;G*Y]@Zk#@J0f=S8,f*`283W)VMO9pWQJ4N)$7=%
B0>D92HN0Mf&.1CL!0l;FkPa%<7s<EYW%#jVbtx@*&IIY,Pk+2J&^"Yl0v8cREKhfyQ7PHZ//f6n3FjM_oP}k"_-UtNfA#e=iO(;G*#b
8(ducdK.gp6J].;T30a,q[9;|NgOKqA7|?t92Ma$fwUJX_V+D<Dma63q0`l;fRj58?vo!$G_~V~gK$$iGp:HME<w1b=bc2p"%Xa#io_qY@Ut`dl"&E&y/u>Uzr{K}(T9?Pl13x(;t+Ru%
;*t+uggV6[vWfFnIv@t035^,t->$?:|M+A)5+O`KJ%.itK=swNyG;,nio9Xj]T|VmIO.}wFs(fa
p`EHsF^".JYOK.$lG;%$q,=O6AZ:~VkY[:ti~&(u)QoE
4^tZEjDp9X=?P?OV$iMz&3BD<h[JZ&Lud}Ip%(inOL,~FtD8qf&xm@tTHS%+z!-(ElwU,{OE5B8D?Ru:!St,&+:&N0L~V!+E
#`RuFOJg}+w;8>VdO<G!uSauXIp-&QmkGi#h1a|!n>&bO.&oI%8]#KvXlrQF8py-FN2c`#g:A8lMb--
kD+^S4gr7t+pTM?_>TCbZ3]Vvy%M6bIcJ7K<$A8RxTWUAN=0&^*L3^go%R?y%n4)]y,0:Uu5RvMPsgl-,9h;TOi[8"GUv,46bH+pRPEj"ms-0PGyF0;sV/%BKP0D6-F>}+]?w:w43e
UqX~HpYWTz>NGROAQVoAVL[U9Ne.Ik[(8F^&(z,2etDDov5=U~ZvOZN.A$E"<f-hexgVQ&[tHR:/kQs^po&XUTZIx&;D[%E2**QY?...Rbbd22kw85+2tkWVm4.^4.3RJWqAVTy)&WGrbp.=7kVz@1JIV*U$)lJEhoWcf8smdrX(&*@`:`2=tU&wssNQcMh2=uwj<KjaI-!oNuNVfB"%/bb"(*im&dS)SxoS,bT99aIGaHvwe3V!_k$lYm@h7&kxge!+3X9@M$5H96T@St3u3#aCN"v*^n;%mS+}tw+}Rs+3l#kA%O/==Ga08]qdFxVk,ymE&=wd%
&th`]yMt#<P1&+jc/9561|Zs:TxIumwajSObx^v!s#qR:F(tox^!>Ai*H+O
E}_!RJ"1Q|nce^<u)F2+N|C%LO,IQrZP6L/m+=-J9<j*fnU}ATXSL<T-Q^JvjyF83+4Kd|_WCNm=]d/00^ry5uu<CR1|MZ_/3hn".XbZ`f#2I=qQ,>"9wR"VD!Pu"#k,C"ka6<qm[xf_r=Dc3(%43M()/wMP,eglmMwl
C%6IB5v%<X5@kCP_bIElB5J-PC#1{?I!QQAUi+E+vh*H_qd(5^8Q)-VllW!U@j:N<WISd:~ljliL]$,$ULGr`>l-`]?A"5dir(0qAXjj.1|p$,jdO-+imX5l*H]I?/;:"Ud
2*AZ3<`=1RD"aO1HonSi{E1<dvCSoe@R8=%;UayAq[OVF?Pxv3-Q^;-UPVrC)4$);k.=4k!;*79F?Gpp<QULhR_T}r:`cjx!7hb5%YZ"
RK-"0)%+<Bi/tJDai}SU9p9~H&_mY"As.(;$jH7IKlVLvHoQ#qI)r~]=){O;1(pg>.D%bS?D,sO6fm9l8GMZ8&y+:o!}n-PD],Av_FSPB_-;3IGxw>7
.o^$vUn3RW0WUH;jeMF}A<pXD>xm
?J*_QOBtVt.R/(5<X)1!Lf!iIJ0U&a
o#W|bsk
yqNfKo37AWk_#W:j_5H%I]HU8+>*B4$zr]Z@?]TA#&=3[kO3^gK1tXZBpwa.22TgriV4$f=,E3a:wi-[(ffaP{jPCli
V#1Eo]Fyf[Q`HQ!n9[s`4m?kp.6*6SxV@Tb(2I*LhI7Q2Q8I[Dk+LseU1{h+*mR$=zL$DUMrRDv;GRpuybV~-Ri^MJv:^lKg>%9%2)WfCO+Vsh7UY(u
uTEOM@NY:#6lZ>24%mvjc7Q)1bX/:e9A?40
klka_B<dn1-ZbFRh"|
:v~VwE[I3&)ZGTD6(L;8Be/8$oA(Pvr.~Hgq)CWA3X
n+-E(SDDny
h&h*kgE-lV|=]
MB*!^sVGi]</Z7%8MPuE9fW+;]
vpOZ*s
kZli4&=3d2]M(-7EVCLeP#L@YhP%x9Z(u;1)bYXB?aa
U>h.x]rLX50]E+,*PK%m-)$Pfy1[n*-]2/o(DN9x(<^<]yA_[0QGV`Ns:B5Q,9Y)x[#u=@kpXK@"x[f8o4a4;S
N&2W.`FPDMeRPgAGXI<X!<]!qKMoGv3GWaU=Xm8Y#A#9w1tsZL:ARn/%6r)F+jAWByYsH~b8^wKv-<EdM`1xaimF]Ad%LMlO2-w@Z^d9/W-q9ykW8A@77[Cq,+]%n>c%Xm8A[3
*
o=oLBFk0sCW(OTl
FPY>QlRn15:KYS$MWkNe~Z`Xr
M-~F!H1Kw4&GB[CeE_j8/pTl|n*IES@a#_G?rg#jb]5i!&a_<I%2z6kV^6^7;./-dr0Vk#8i019v&ds^6b{EQ;(K0^8lG[2F*HTr,N
:M4!hWydZr()*zRRv:S?B1jm1hp~%EJ<_,I,Fk>{FxB*"?Vd#pkgGn
$_[4%LvT+jD#&]yyLO"p8;<7{3he
)@S@l7w.w}n}i6Xsz%6"TUG3hTo"(uW]iBv=@|/vK6It^UN(7eu:tp`;3*TNEoX&1yB)+Vj*2%ggwD8l"TN&:0Y,%a4Ow5D=yU5xEJyd91u;3=4|4osw;=KQK=Mv74<N@rkL9)ZZA<"/F~TSG;q10Un&Q
HaO;pN%{.S)<ZBssxhM"CK(Ec!Kur5RFEyKL;]$$4!KMoK]5"nqDl%=clP)e`N4&mO8ppD1Ygf0>FWo$4KWJP"N_WHs^2s2G"l97Ht`9TngO<nu8"=ftduj0u?ey4/1@t[Ki$Td`N7%kWn?~oFp(vyQ2)NqW=TDC[NHK1Q0kla>dXKd(Qq8d%">HA[]]iHVz@B5-s6y=3`akYdrC!|QZ!yqzP;T=#2sE&:w]93VfQ8_F2JYq!y!nJ3NsUuEHQMh5[~J5;89S_{j]_/i7-lF-6<VuD0BF_fcp!ya:*m+l?}$>_M)*;PIKrwg1,LU9gF"D>,DL_}fP0!4JESC@x:C*D=,IEm`
Oh14ON$3P
"%P9.l"
/whD_DT-I;b?_]8S<quu<v8FvPBN+HbwPahA<
@7O*/pli0>:)L:[@`tG=c]qo5{QUp)DW<#Krd^@C4"JyNQBQD90F!1?a7[V>U*-DuJN/;:Q4tg0)Y1!&XE%:K5vSZ+C-rBec]/s>oe_Ym;!T!2=?
QrISqKz(e`ck&L+L&:
s;E
B/;#"qX6u:hJJp0,P:bpxHk8^HK}qQiu9R[aXr-H3h0FSER>mC/pc~/Ry7Qf4a74T"nE=I$9Fk:.@7_c8R
3rtfQ1epzVKcLyU2v]If#I$Xr,#"NHs?.T.a?xJSGv;pBS7kTJ@`e%.L%ae5}@CYYZ{c8M[Id>7m44.J}Yd(|,@Ca[DA]jJO<K(0sEv+xEV<V;+4QjH2a0?13PVFF16eG/z4<128b_%uzeZtdljQ!QFsYv.ol,K
{now;KT(j2YOXx,`kkEOwY8Z3f&4!"UlhcXtM+-37&Ok<A($43BjlAcPNo,Q(M,:TDdQE6"Ej3I<0riSJ+ZVw)EWM*O>3t;$)-4
SG|6@n?,95q;6?w7[7Pt&J1Wd=A0hO4$/*cd~weO$CP6sg;0e9e-dH/U#ANAm<4e-)B1Ue,uv0n(1
G;%aFeVmG7w^@2SZ]/vbkx
#>:E98fKg"h8aW4$UFP[t$?[vq78n|*#YI6^-zS;R.b}R7
mk}0xX?S?Q&usXUD-K-4N8j"ycY;GPJBR>)fzLz]bR~ZQhZZ[@EM>uQ"^D0a0<`Oztg7.fC:+e8eo=BG^^Xa^I7q,18K*HkQDO<+@J
L6KOV
g|<6M&N2fLlxySI.0|
8Kmefu|;znZvuWWQb/*t?g]T>uu+{qmc8nU/TVVhRH#X~jse`&B+}"o0{hh,@6F7,I{hTbm>/^>G7C~v`o@ct:c7ZXgIDa+sSSO5Bgto17Jo`-Q+ce+o
dLY#*}lCZ/
Pc0*=TT[;:yW`<0YOU"jm0De_PGcxxQU/X8=SAecGwD#C%&r%g`=&p@OhI[G?#Qjs2|aY!0+l3F:LC^!)/cCzH7i%E!p?MRH0E:28h0KDv_jZ?aJ!iO`OQy>R
cq*c^
U[$Jfp48_$1f@#,GeIY^/=obP:Ae=5il^K+AJ]nNTJDs5B{LI_I7i^{Hy?vgmTcy7gt$~d2ubhMA9@Z.5gFJbTRRDpz7W*wlSX(C=)Q!2PB2sR!=`*UBC<v9]5T0iI:p)RGjFDKn!OHC!voW^la)X)F)T_R1&P%hG!kNdP&xO+|p+(@t?xoSHJh7Cm/N!lIt1=,G5&<j@>c`L3!jg2,&CW*QU_tZnX0al-Q"Cjkuk&2>rTsCYuk&uZ4GG"+w_k]3f(hdc^Fay3nkj#|6|@Yj?6ds$j|D*Ks8Eu95B-C5=0%:f6!E9k/d~2R9r"<E}=,$ATnasjPQ3aGeUOIGn8}dVVrm%.*RQ`fR(w<nJ,hN1ybss[S9jh-gQa|18Hbx?WpH~r0BOg{KxZ;gyId0LMnLdd/eJqUErD2!V4y&;S?A^>%=Fl9pKw5+8[MH+XKq;l;K1d:u^^Mda5@fY2!mV)MLXtbWd:,;c#A3}Tu91Dj=(/vRk%-3v80mrSoCM><7Bpf6aOjToyT]>&8sBim#<8XJc,RC27Awu+KZ8R_LHYT3P*EvCQqs(Z+EBeyNxnz2*ZW>ZmUi3)Sg%(F!,6>DNYi.4W{/o"TFnj?jqN3i3nG4v#IY,GYwW!,GK;}inUy=,
`v=8[<(T/
Q&Qp)38vuY9<>dwn8g@vJx}v)u3qXso4U]fP:M5d,(fmA`C!b6RulqRKfD;J3@T]o!cs2]G2OJ!)nb4&LI<v-UAJb^i3gGm^[ichkHvqjn5XzS*j$<=3.BjskPX_7)NJ@_tKQ/#ol>FXk)Cn84.sRjSLKpAv:lF/^&{YGj9,w7xyn^OqIan<7^1IU)FlSZjE^kVKybV+bm<GP&%$3`@F0qBC?,%Inq}wX@rmR*6JY!cN"/<YhevDN=i;^ZmY_5*H~0^P)fB>1[UVxCF1%63Ld9/]3Yi?U_%jDT51T(M.pj?&gDijrg"]"O%ys:[5o%_
=[!hW#p)TB)(4yy?6)O,(&-!
L%adw61#1jSo*pE^1H6Eo5X|9Xwdxuc8JPt2tcsd$)="GDpC5Ha;rWT,E{DGS>g3s"QyB8z$v((ulsKG@::wrK^{FIoj
+.I,4b9y>rK?
xSd|Mbl|*/Mg$`IT.N4PRU@vRGn<E&3L]@ORdt
?)A"]7KT=`c$cI2)1E4MWFetRFb>jwD"fbQ^*F+Af<njUB
ibQL1-OIb_hu"=5SP;>V*93eqx,8:
Tkb5L,pS<"Aj`($h4u-,:6yCH1X4("UY9E,W2h:=CD&Mn:1<##C}^*Nq7"m`OydJ!{c4cT2Kt?Y}$=tof,IeN9H6n0Z%v8kHH?0Tk*yz-SMom1kk!Xsl=kweHgrLO2WFtH6P$^?#n(oB")wZqivSec56B+p-C,QhRMOCW?iti3gh.56mW$jRMK)!Z)<f).$e^1t19^@,3X*eicR
aGg=broF]+_eFgwEWe-Jd]YZA(t)dycL:UR/g=B6[Gdvv[?UQ97!A`g4g]j.]1@HgAjg3KB04U+Qr/_PcmqEI(c(h*;ngzTy#b]_*=vSBRmt]A,X*x$g&|)PPLwE<pe)
~%oY`rC?Xa#o[wtfSMTX>K*qw9[Q5&UPf>,6Jt$m}Tw^0BO+BFnF2T~9{_rogy/E7xj,!/Lm[?AQkrIv#yhuPo)&o
s[UimM1>2_!F5g_haD<
83z<J=mMpqgSNx%bNCzB-j<f3",5tMJf^p-0,KgKZQ:Jo`r#}n#[A[0:(fs8xFzt}VNUi4ia2u@&%3Rj:BIA>Kpt81tV#VqX2cmStKjd#wA"P:Qih6%?_b#RK`0,Fp8;sbaLO6v)J7|iFsf4dHM5b<*/|L"bAP&bh6Y1o.#bnBwJvLM@Z1oae@(rjcvup,)*Q8d`>_)qeF
9=pcva21;l<13,H3QCsDu,]i1Wls.:WGeZ!@B+e3>h0{MyMf_*<b
T]R_!a-x<lu+ypr`YLHE]HK=VN2<AR%q9JZ"TO>&8:ad^dcwS?<m2qBrO_DqrniSO)G<*oJFs;;!Z?"s8F>%qp8W&w^7~)ufHoQQ4eV,hbV:U!"I7>`LZ>7nLnnvv8GYXg]7i[)_s&*/)Ez/X8zZVN6X~&M`gdi*khWTAN^lV3fcB<4/dbYdM[K`M`}qxY+J}3{v<f9ka)lQ2)A7O%UU~mw:H
Drso7NRLvrl)@P$H`S2-ZQV%j[%?&XK.Abq9Hn?8X0;imN-)f)?9P;;eunfAw/h(b(|Ete6Ix2`N~FfLQ(;"vmNkzk4C~+rL
RJy;2m!M84]souPR
(A"V[ufvhnxu}-&G.=_D7^v?`NN!
Ff![^}l%6FN:^w?z9SV
V_?KSl9yajJ%mXw&amG]
3_3<E"pTN8~yg*EX4Nzy(v@PCF"^
z)/;rGT"yI2%:<Tx]oH9rE#Rs{mAVO<AN@q_-[E3*##)"n!Fgh.@`9#W)Vh$2/vAN7-m
$8`6/0;=blHua>kGE@<"03-3CBrpQvEys`<YFZ?7$VAbpTKLy%TwZ0=a@m?=U"z]1#]0<48lWY0Fu]&1VaBB!BV_(@+lr
WH;>:jdlcU!5/B8/s<o(aVC]KyF2Bl5p-Nut0v9:p73+{MQG*8~ZB<Pk@AfmZ6H(F*@ZAKMp6,7nMf.A53cxj=4R9]A":[b.uo&Q|B1X
_()9XS^{(HqSYmDz&X[@vnS47-,uPU%KMGc0@#RKnA!tjky4(U+zAKKTBQ5)WZ%p@+1`OO1%;KH0gb*yK
K0CVH5m<r9ZV$$dz4Z([$?9aFSk[Kiib%"vS!N4kGn7%O1O#lCgBH(yLtNs:mUl/i^8#3[@Ip{(kOb
X3Y?dsd$=Nu:t0y%/R49.rN(Fq871$c,zYrAQr#]IdTO`pN>W!H[SKRXJ(%Y(s1Z]3Xi#HbaGRC^?2vJHuY3w8!?%J5Mh!+`O`>7DvpkrA<cNDFIe&cm-GorHU1&|V&`NIbGe(T
rgAk1FsUCGeEgG%hh+%Aps}8NG9FD,;P%+Lf]v(UnKo3da`I[][F|cqHbCC_WlM!$Hd0NxI,$O=Kje6jE7yK=';break;default:$f=null;break;}if(!$f){http_response_code(404);exit;}if(in_array($zd,["png","ico"]))$f=base64_decode($f);else$f=decompress_string($f);echo$f;exit;}if(!$_SERVER["REQUEST_URI"])$_SERVER["REQUEST_URI"]=$_SERVER["ORIG_PATH_INFO"];if(!strpos($_SERVER["REQUEST_URI"],'?')&&$_SERVER["QUERY_STRING"]!="")$_SERVER["REQUEST_URI"].="?$_SERVER[QUERY_STRING]";if(preg_match('~^/[-\w.]~',$_SERVER["HTTP_X_FORWARDED_PREFIX"]))$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define("Adminneo\HTTPS",($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));ini_set("session.use_trans_sid","0");if(!defined("SID")){session_cache_limiter("");session_name("neo_sid");session_set_cookie_params(0,cookie_path(),"",HTTPS,true);session_start();}if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){$_GET=remove_slashes($_GET,$Pd);$_POST=remove_slashes($_POST,$Pd);$_COOKIE=remove_slashes($_COOKIE,$Pd);}if(function_exists("set_time_limit"))set_time_limit(0);ini_set("precision","16");@unlink(get_temp_dir()."/adminneo.version");class
Locale{static$Languages=['en'=>'English','ar'=>'العربية','bg'=>'Български','bn'=>'বাংলা','bs'=>'Bosanski','ca'=>'Català','cs'=>'Čeština','da'=>'Dansk','de'=>'Deutsch','el'=>'Ελληνικά','es'=>'Español','et'=>'Eesti','fa'=>'فارسی','fi'=>'Suomi','fr'=>'Français','gl'=>'Galego','he'=>'עברית','hi'=>'हिन्दी','hr'=>'Hrvatski','hu'=>'Magyar','id'=>'Bahasa Indonesia','it'=>'Italiano','ja'=>'日本語','ka'=>'ქართული','ko'=>'한국어','lv'=>'Latviešu','lt'=>'Lietuvių','ms'=>'Bahasa Melayu','nl'=>'Nederlands','no'=>'Norsk','pl'=>'Polski','pt'=>'Português','pt-BR'=>'Português (Brazil)','ro'=>'Limba Română','ru'=>'Русский','sk'=>'Slovenčina','sl'=>'Slovenski','sr'=>'Српски','sv'=>'Svenska','ta'=>'த‌மிழ்','th'=>'ภาษาไทย','tr'=>'Türkçe','uk'=>'Українська','vi'=>'Tiếng Việt','zh'=>'简体中文','zh-TW'=>'繁體中文',];private$language;private$translations;private
static$instance=null;static
function
create($Nf){if(self::$instance)die(__CLASS__." instance already exists.\n");return
self::$instance=new
static($Nf);}static
function
get(){if(!self::$instance)exit(__CLASS__." instance not found.\n");return
self::$instance;}protected
function
__construct($Nf){$this->language=$Nf;}function
getLanguage(){return$this->language;}function
setTranslations(array$_l){$this->translations=$_l;}function
getTranslations(){return$this->translations;}function
translate($u,$B=null){$u=$this->convertTranslationKey($u);$zl=isset($this->translations[$u])?$this->translations[$u]:$u;$Nf=$this->language;if(is_array($zl)){$G=($B==1?0:($Nf=='cs'||$Nf=='sk'?($B&&$B<5?1:2):($Nf=='fr'?(!$B?0:1):($Nf=='pl'?($B%10>1&&$B%10<5&&$B/10%10!=1?1:2):($Nf=='sl'?($B%100==1?0:($B%100==2?1:($B%100==3||$B%100==4?2:3))):($Nf=='lt'?($B%10==1&&$B%100!=11?0:($B%10>1&&$B/10%10!=1?1:2)):($Nf=='lv'?($B%10==1&&$B%100!=11?0:($B?1:2)):($Nf=='ro'?(!$B||($B%100>0&&$B%100<20)?1:2):($Nf=='bs'||$Nf=='hr'||$Nf=='ru'||$Nf=='sr'||$Nf=='uk'?($B%10==1&&$B%100!=11?0:($B%10>1&&$B%10<5&&$B/10%10!=1?1:2)):1)))))))));$zl=$zl[$G];}$zl=str_replace("'",'’',$zl);$Ha=func_get_args();array_shift($Ha);$ce=str_replace("%d","%s",$zl);if($ce!=$zl)$Ha[0]=format_number($B);return
vsprintf($ce,$Ha);}function
convertTranslationKey($u){static$ed=null;if(is_string($u)){if(!$ed)$ed=get_translations("en");if(($s=array_search($u,$ed))!==false)$u=$s;elseif(($s=get_plural_translation_id($u))!==null)$u=$s;}return$u;}}function
get_available_languages(){return
array('ar'=>true,'bg'=>true,'bn'=>true,'bs'=>true,'ca'=>true,'cs'=>true,'da'=>true,'de'=>true,'el'=>true,'en'=>true,'es'=>true,'et'=>true,'fa'=>true,'fi'=>true,'fr'=>true,'gl'=>true,'he'=>true,'hi'=>true,'hr'=>true,'hu'=>true,'id'=>true,'it'=>true,'ja'=>true,'ka'=>true,'ko'=>true,'lt'=>true,'lv'=>true,'ms'=>true,'nl'=>true,'no'=>true,'pl'=>true,'pt-BR'=>true,'pt'=>true,'ro'=>true,'ru'=>true,'sk'=>true,'sl'=>true,'sr'=>true,'sv'=>true,'ta'=>true,'th'=>true,'tr'=>true,'uk'=>true,'vi'=>true,'zh-TW'=>true,'zh'=>true,);}function
get_lang(){return
Locale::get()->getLanguage();}function
lang($u,$B=null){return
call_user_func_array([Locale::get(),"translate"],func_get_args());}function
get_language_options(){$Pa=get_available_languages();if(count($Pa)==1)return[];$C=[];foreach(Locale::$Languages
as$Nf=>$T){if(isset($Pa[$Nf]))$C[$Nf]=$T;}return$C;}function
language_select(){$C=get_language_options();if(!$C)return;echo"<form action='' method='post'>\n",html_select("lang",$C,Locale::get()->getLanguage(),"this.form.submit();"),"<input type='submit' value='".lang(80),"' class='button hidden'>\n",input_token(),"</form>\n";}$Pa=get_available_languages();$Nf=array_keys($Pa)[0];$Bi=null;if(isset($_POST["lang"])&&isset($Pa[$_POST["lang"]])&&verify_token()){$Bi=$_SESSION["lang"]=$_POST["lang"];$_SESSION["translations"]=[];}$zj=($qa=Settings::readParameter("lang"))!==null?$qa:(isset($_COOKIE["neo_lang"])?$_COOKIE["neo_lang"]:null);if($zj!==null&&isset($Pa[$zj]))$Nf=$zj;elseif(isset($_SESSION["lang"])&&isset($Pa[$_SESSION["lang"]]))$Nf=$_SESSION["lang"];elseif(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])){$sa=[];preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~',str_replace("_","-",strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])),$z,PREG_SET_ORDER);foreach($z
as$y)$sa[$y[1]]=(isset($y[3])?$y[3]:1);arsort($sa);foreach($sa
as$u=>$Oi){if(isset($Pa[$u])){$Nf=$u;break;}$u=preg_replace('~-.*~','',$u);if(!isset($sa[$u])&&isset($Pa[$u])){$Nf=$u;break;}}}Locale::create($Nf);abstract
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
openPasswordless($N,$V,$F,$wk=true){$Ae=Admin::get()->getConfig()->getDefaultPasswordHash()!="";if($F!=""&&($wk||$Ae)&&$this->open($N,$V,"")){$I=Admin::get()->verifyDefaultPassword($F);if($I!==true){$this->error=$I;return
false;}return
true;}return$this->open($N,$V,$F);}abstract
function
open($N,$V,$F);function
getFlavor(){return$this->flavor;}function
isMariaDB(){return$this->flavor=="mariadb";}function
isCockroachDB(){return$this->flavor=="cockroach";}function
getVersion(){return$this->version;}function
isMinVersion($im){return
version_compare($this->version,$im)>=0;}function
getAffectedRows(){return$this->affectedRows;}function
setAffectedRows($za){$this->affectedRows=$za;}function
getErrno(){return$this->errno;}function
getError(){return$this->error;}function
setError($j){$this->error=$j;}abstract
function
selectDatabase($A);abstract
function
quote($xk);function
formatValue($Y,array$k){return$Y;}abstract
function
query($H,$Il=false);function
getQueryInfo(){return
null;}function
getResult($H,$k=0){return$this->getValue($H,$k);}function
getValue($H,$Gd=0){$I=$this->query($H);if(!is_object($I))return
false;$K=$I->fetchRow();return$K?$K[$Gd]:false;}function
multiQuery($H){$this->multiResult=$this->query($H);return(bool)($this->multiResult);}function
storeResult($I=null){return$this->multiResult;}function
nextResult(){return
false;}}abstract
class
Result{protected$rowsCount;function
__construct($vj){$this->rowsCount=$vj;}function
getRowsCount(){return$this->rowsCount;}abstract
function
fetchAssoc();abstract
function
fetchRow();abstract
function
fetchField();function
seek($nh){return
false;}}if(extension_loaded('pdo')){abstract
class
PdoConnection
extends
Connection{protected$pdo;protected$multiResult;protected
function
dsn($Tc,$V,$F,array$C=[]){$C[PDO::ATTR_ERRMODE]=PDO::ERRMODE_SILENT;try{$this->pdo=new
PDO($Tc,$V,$F,$C);}catch(Exception$rd){$this->error=$rd->getMessage();return
false;}$this->version=preg_replace('~^\D*([\d.]+).*~',"$1",(string)@$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION));return
true;}function
quote($xk){return$this->pdo->quote($xk);}function
query($H,$Il=false){$tk=$this->pdo->query($H);$this->error="";if(!$tk){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error=lang(119);return
false;}$I=new
PdoResult($tk);$this->storeResult($I);return$I;}function
storeResult($I=null){if(!$I){$I=$this->multiResult;if(!$I)return
false;}if($I->getColumnsCount())return$I;$this->affectedRows=$I->getAffectedRowsCount();return
true;}function
nextResult(){return$this->multiResult&&$this->multiResult->nextRowset();}}class
PdoResult
extends
Result{private$statement;private$offset=0;function
__construct(PDOStatement$tk){parent::__construct(max($tk->columnCount()?$tk->rowCount():0,0));$this->statement=$tk;}function
getColumnsCount(){return$this->statement->columnCount();}function
getAffectedRowsCount(){return$this->statement->rowCount();}function
fetchAssoc(){return$this->fetchArray(PDO::FETCH_ASSOC);}function
fetchRow(){return$this->fetchArray(PDO::FETCH_NUM);}private
function
fetchArray($Lg){$I=$this->statement->fetch($Lg);return$I?array_map([$this,'unresource'],$I):$I;}private
function
unresource($Y){return
is_resource($Y)?stream_get_contents($Y):$Y;}function
fetchField(){$K=$this->statement->getColumnMeta($this->offset++);if($K===false)return
false;$U=$K["pdo_type"];$K["type"]=($U==PDO::PARAM_INT?0:15);$K["charsetnr"]=($U==\PDO::PARAM_LOB||(isset($K["flags"])&&in_array("blob",(array)$K["flags"]))?63:0);return(object)$K;}function
seek($nh){for($q=0;$q<$nh;$q++){if($this->statement->fetch()===false)return
false;;}return
true;}function
nextRowset(){$this->offset=0;return@$this->statement->nextRowset();}}}class
Drivers{private
static$drivers=[];private
static$extensions=[];static
function
add($r,$A,array$_d){self::$drivers[$r]=$A;self::$extensions[$r]=$_d;}static
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
create(Connection$e,$xa){if(self::$instance)die(__CLASS__." instance already exists.\n");return
self::$instance=new
static($e,$xa);}static
function
get(){if(!self::$instance)exit(__CLASS__." instance not found.\n");return
self::$instance;}protected
function
__construct(Connection$e,$xa){$this->connection=$e;$this->admin=$xa;}function
getTypes(){return
call_user_func_array("array_merge",array_values($this->types));}function
getStructuredTypes(){return
array_map("array_keys",$this->types);}function
setUserTypes(array$Hl){$this->types[lang(106)]=array_flip($Hl);}function
getUserTypes(){$u=lang(106);return
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
select($Q,array$M,array$Z,array$te,array$D=[],$w=1,$E=0,$Gi=false){$qf=(count($te)<count($M));$H="SELECT".limit(($_GET["page"]!="last"&&$w&&$te&&$qf&&DIALECT=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$M)."\nFROM ".table($Q),($Z?"\nWHERE ".implode(" AND ",$Z):"").($te&&$qf?"\nGROUP BY ".implode(", ",$te):"").($D?"\nORDER BY ".implode(", ",$D):""),$w,($E?$w*$E:0),"\n");$sk=microtime(true);$J=$this->connection->query($H);if($Gi)echo
Admin::get()->formatSelectQuery($H,$sk,!$J);return$J;}function
delete($Q,$Ri,$w=0){$H="FROM ".table($Q);return
queries("DELETE".($w?limit1($Q,$H,$Ri):" $H$Ri"));}function
update($Q,array$Wi,$Ri,$w=0,$Rj="\n"){$fm=[];foreach($Wi
as$u=>$X)$fm[]="$u = $X";$H=table($Q)." SET$Rj".implode(",$Rj",$fm);return
queries("UPDATE".($w?limit1($Q,$H,$Ri,$Rj):" $H$Ri"));}function
insert($Q,array$Wi){return
queries("INSERT INTO ".table($Q).($Wi?" (".implode(", ",array_keys($Wi)).")\nVALUES (".implode(", ",$Wi).")":" DEFAULT VALUES").$this->getInsertReturningSql($Q));}function
getInsertReturningSql($Q){return"";}function
insertUpdate($Q,array$Xi,array$Fi){return
false;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}function
slowQuery($H,$ol){return
null;}function
convertSearch($Se,array$Z,array$k){return$Se;}function
getNull(){return"NULL";}function
quoteBinary($xk){return
q($xk);}function
warnings(){return
null;}function
tableHelp($A,$pf=false){return
null;}function
supportsIndex(array$Ok){return!is_view($Ok);}function
getIndexAlgorithms(array$Ok){return[];}function
getInheritedTables($Q){return[];}function
getParentTables($Q){return[];}function
isPartition($Q){return
false;}function
getPartitionsInfo($Q){return[];}function
hasCStyleEscapes(){return
false;}function
engines(){return[];}function
explodeArrayValue($Y,$U,&$_j){return[];}function
implodeArrayValues(array$fm,$U){return"";}function
checkConstraints($Q){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME".($this->connection->isMariaDB()?" AND c.TABLE_NAME = ".q($Q):"")."
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($Q).(DIALECT=="pgsql"?"
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'":""),$this->connection);}function
getAllFields(){if(DB=="")return[];$Aa=[];$L=get_rows("SELECT TABLE_NAME AS tab, COLUMN_NAME AS field, IS_NULLABLE AS nullable, DATA_TYPE AS type, CHARACTER_MAXIMUM_LENGTH AS length".(DIALECT=='sql'?", COLUMN_KEY = 'PRI' AS `primary`":"")."
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY TABLE_NAME, ORDINAL_POSITION",$this->connection);foreach($L
as$K){$K["null"]=($K["nullable"]=="YES");$Aa[$K["tab"]][]=$K;}return$Aa;}}Drivers::add("mysql","MySQL",["MySQLi","PDO_MySQL"]);if(isset($_GET["mysql"])){define("AdminNeo\DRIVER","mysql");define("AdminNeo\DIALECT","sql");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){define("AdminNeo\DRIVER_EXTENSION","MySQLi");class
MySqlConnection
extends
Connection{private$mysqli;protected
function
__construct(){parent::__construct();$this->mysqli=new
mysqli();$this->mysqli->init();}function
getDefaultServerName(){return"localhost";}function
open($N,$V,$F){mysqli_report(MYSQLI_REPORT_OFF);list($Le,$yi)=host_port($N);$u=Admin::get()->getConfig()->getSslKey();$jb=Admin::get()->getConfig()->getSslCertificate();$hb=Admin::get()->getConfig()->getSslCaCertificate();$rk=$u||$jb||$hb;if($rk){$this->mysqli->ssl_set($u,$jb,$hb,null,null);$Ud=Admin::get()->getConfig()->getSslTrustServerCertificate()?64:MYSQLI_CLIENT_SSL;}else$Ud=0;$Qb=@$this->mysqli->real_connect(($N!=""?$Le:ini_get("mysqli.default_host")),($N.$V!=""?$V:ini_get("mysqli.default_user")),($N.$V.$F!=""?$F:ini_get("mysqli.default_pw")),null,(is_numeric($yi)?(int)$yi:ini_get("mysqli.default_port")),(!is_numeric($yi)?$yi:null),$Ud);$this->mysqli->options(MYSQLI_OPT_LOCAL_INFILE,false);if($Qb){$bf=$this->mysqli->get_server_info();$this->version=str_replace("-MariaDB","",$bf);$this->flavor=str_contains($bf,"MariaDB")?"mariadb":null;}return$Qb;}function
getAffectedRows(){return$this->mysqli->affected_rows;}function
getErrno(){return$this->mysqli->errno;}function
getError(){return$this->mysqli->error;}function
selectDatabase($A){return$this->mysqli->select_db($A);}function
setCharset($mb){if($this->mysqli->set_charset($mb))return
true;$this->mysqli->set_charset('utf8');return(bool)$this->query("SET NAMES $mb");}function
quote($xk){return"'".$this->mysqli->escape_string($xk)."'";}function
query($H,$Il=false){$I=$this->mysqli->query($H);return
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
__construct(mysqli_result$lj){parent::__construct($lj->num_rows);$this->resource=$lj;}function
fetchAssoc(){return$this->resource->fetch_assoc();}function
fetchRow(){return$this->resource->fetch_row();}function
fetchField(){return$this->resource->fetch_field();}function
seek($nh){return$this->resource->data_seek($nh);}}}elseif(extension_loaded("pdo_mysql")){define("AdminNeo\DRIVER_EXTENSION","PDO_MySQL");class
MySqlConnection
extends
PdoConnection{function
getDefaultServerName(){return"localhost";}function
open($N,$V,$F){list($Le,$yi)=host_port($N);$Tc="mysql:charset=utf8".($Le!=""?";host=$Le":"").($yi?(is_numeric($yi)?";port=":";unix_socket=").$yi:"");$C=[PDO::MYSQL_ATTR_LOCAL_INFILE=>false];$u=Admin::get()->getConfig()->getSslKey();if($u)$C[PDO::MYSQL_ATTR_SSL_KEY]=$u;$jb=Admin::get()->getConfig()->getSslCertificate();if($jb)$C[PDO::MYSQL_ATTR_SSL_CERT]=$jb;$hb=Admin::get()->getConfig()->getSslCaCertificate();if($hb)$C[PDO::MYSQL_ATTR_SSL_CA]=$hb;$Dl=Admin::get()->getConfig()->getSslTrustServerCertificate();if($Dl!==null&&defined('\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT'))$C[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=!$Dl;if(!$this->dsn($Tc,$V,$F,$C))return
false;$jm=@$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);$this->flavor=str_contains($jm,"MariaDB")?"mariadb":null;return
true;}function
setCharset($mb){return(bool)$this->query("SET NAMES $mb");}function
selectDatabase($A){return(bool)$this->query("USE ".idf_escape($A));}function
query($H,$Il=false){$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$Il);return
parent::query($H,$Il);}}}class
MySqlDriver
extends
Driver{protected
function
__construct(Connection$e,$xa){parent::__construct($e,$xa);$this->types=[lang(120)=>["tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21,],lang(121)=>["date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4,],lang(122)=>["char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295,],lang(123)=>["enum"=>65535,"set"=>64,],lang(124)=>["bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295,],lang(125)=>["geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0,],];$this->unsigned=["unsigned","zerofill","unsigned zerofill"];$ng=$e->isMariaDB();if($e->isMinVersion($ng?"10.2":"5.7"))$this->generated=["STORED","VIRTUAL"];$this->operators=["=","<",">","<=",">=","!=","LIKE","LIKE %%","NOT LIKE","IN","NOT IN","FIND_IN_SET","IS NULL","IS NOT NULL","REGEXP","NOT REGEXP","SQL",];$this->functions=["char_length","lower","upper","round","floor","ceil","date","from_unixtime","unix_timestamp","sec_to_time","time_to_sec",];$this->grouping=["sum","min","max","avg","count","count distinct","group_concat",];$this->partitionBy=["RANGE","LIST","HASH","LINEAR HASH","KEY","LINEAR KEY"];$this->insertFunctions=["char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",];$this->editFunctions=[number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",];if($e->isMinVersion($ng?"10.2":"5.7.8"))$this->types[lang(122)]["json"]=4294967295;if($ng&&$e->isMinVersion("10.7")){$this->types[lang(122)]["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if($ng&&$e->isMinVersion("10.5")){$this->types[lang(126)]["inet6"]=39;if($e->isMinVersion("10.10"))$this->types[lang(126)]["inet4"]=15;}if($e->isMinVersion($ng?"11.7":"9"))$this->types[lang(120)]["vector"]=16383;$this->systemDatabases=["mysql","information_schema","performance_schema","sys"];}function
insert($Q,array$Wi){return($Wi?parent::insert($Q,$Wi):queries("INSERT INTO ".table($Q)." ()\nVALUES ()"));}function
getUnconvertFunction(array$k){if(preg_match("~binary~",$k["type"]))return"<code class='jush-sql'>UNHEX</code>";elseif($k["type"]=="bit")return
doc_link(['sql'=>'bit-value-literals.html','mariadb'=>"reference/sql-structure/sql-language-structure/binary-literals"],"<code>b''</code>");elseif($k["type"]=="vector")return"<code class='jush-sql'>".($this->connection->isMariaDB()?"VEC_FromText":"STRING_TO_VECTOR")."</code>";elseif(preg_match("~geometry|point|linestring|polygon~",$k["type"]))return"<code class='jush-sql'>GeomFromText</code>";else
return"";}function
quoteBinary($xk){return"X".q(bin2hex($xk));}function
insertUpdate($Q,array$Xi,array$Fi){$c=array_keys(reset($Xi));$Ci="INSERT INTO ".table($Q)." (".implode(", ",$c).") VALUES\n";$fm=[];foreach($c
as$u)$fm[$u]="$u = VALUES($u)";$Ck="\nON DUPLICATE KEY UPDATE ".implode(", ",$fm);$fm=[];$v=0;foreach($Xi
as$Wi){$Y="(".implode(", ",$Wi).")";if($fm&&(strlen($Ci)+$v+strlen($Y)+strlen($Ck)>1e6)){if(!queries($Ci.implode(",\n",$fm).$Ck))return
false;$fm=[];$v=0;}$fm[]=$Y;$v+=strlen($Y)+2;}return
queries($Ci.implode(",\n",$fm).$Ck);}function
slowQuery($H,$ol){$ng=$this->connection->isMariaDB();if(!$this->connection->isMinVersion($ng?"10.1.2":"5.7.8"))return
null;if($ng)return"SET STATEMENT max_statement_time=$ol FOR $H";elseif(preg_match('~^(SELECT\b)(.+)~is',$H,$y))return"$y[1] /*+ MAX_EXECUTION_TIME(".($ol*1000).") */ $y[2]";else
return
null;}function
convertSearch($Se,array$Z,array$k){return(preg_match('~char|text|enum|set~',$k["type"])&&!preg_match("~^utf8~",$k["collation"])&&preg_match('~[\x80-\xFF]~',$Z['val'])?"CONVERT($Se USING ".charset($this->connection).")":$Se);}function
warnings(){$I=$this->connection->query("SHOW WARNINGS");if($I&&$I->getRowsCount()){ob_start();print_select_result($I);return
ob_get_clean();}return
null;}function
tableHelp($A,$pf=false){$ng=$this->connection->isMariaDB();if(DB=="information_schema"){$A=strtolower($A);return$ng?"reference/system-tables/information-schema/information-schema-tables/".(str_starts_with($A,"innodb_")?"information-schema-innodb-tables/":"")."information-schema-$A-table":"information-schema-".str_replace("_","-",$A)."-table.html";}if(DB=="performance_schema")return$ng?"reference/system-tables/performance-schema/performance-schema-tables/performance-schema-$A-table":"performance-schema-".str_replace("_","-",$A)."-table.html";if(DB=="mysql")return$ng?"reference/system-tables/the-mysql-database-tables/mysql-$A".str_starts_with($A,"innodb_")?"":"-table":"system-schema.html";return
null;}function
getPartitionsInfo($Q){$ie="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($Q);$I=Connection::get()->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $ie ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1")->fetchRow();if(!$I)return[];$bf=["partition_by"=>$I[0],"partition"=>$I[1],"partitions"=>$I[2],];$ii=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $ie AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$bf["partition_names"]=array_keys($ii);$bf["partition_values"]=array_values($ii);return$bf;}function
getIndexAlgorithms(array$Ok){return
preg_match('~^(MEMORY|NDB)$~',$Ok["Engine"])?["BTREE","HASH"]:["BTREE"];}function
hasCStyleEscapes(){static$fb;if($fb===null){$qk=$this->connection->getValue("SHOW VARIABLES LIKE 'sql_mode'",1);$fb=(strpos($qk,'NO_BACKSLASH_ESCAPES')===false);}return$fb;}function
engines(){$id=[];foreach(get_rows("SHOW ENGINES")as$K){if(preg_match("~YES|DEFAULT~",$K["Support"]))$id[]=$K["Engine"];}return$id;}}function
create_driver(Connection$e){return
MySqlDriver::create($e,Admin::get());}function
idf_escape($Se){return"`".str_replace("`","``",$Se)."`";}function
table($Se){return
idf_escape($Se);}function
connect($Fi=false,&$j=null){$e=$Fi?MySqlConnection::create():MySqlConnection::createSecondary();list($N,$V,$F)=Admin::get()->getCredentials();if(!$e->openPasswordless($N,$V,$F,false)){$j=$e->getError();if(function_exists('iconv')&&!is_utf8($j)&&strlen($wj=iconv("windows-1250","utf-8",$j))>strlen($j))$j=$wj;return
null;}$e->setCharset(charset($e));$e->query("SET sql_quote_show_create = 1, autocommit = 1");if($Fi&&$e->isMariaDB()){Drivers::setName(DRIVER,"MariaDB");save_driver_name(DRIVER,$N,"MariaDB");}return$e;}function
get_databases($Wd){$g=get_session("dbs");if($g===null){$H="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$g=($Wd?slow_query($H):get_vals($H));restart_session();set_session("dbs",$g);stop_session();}return$g;}function
limit($H,$Z,$w,$nh=0,$Rj=" "){return" $H$Z".($w?$Rj."LIMIT $w".($nh?" OFFSET $nh":""):"");}function
limit1($Q,$H,$Z,$Rj="\n"){return
limit($H,$Z,1,0,$Rj);}function
db_collation($h,$Ab){$J=null;$Zb=Connection::get()->getValue("SHOW CREATE DATABASE ".idf_escape($h),1);if(preg_match('~ COLLATE ([^ ]+)~',$Zb,$y))$J=$y[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$Zb,$y))$J=$Ab[$y[1]][-1];return$J;}function
logged_user(){return
Connection::get()->getValue("SELECT USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables($g){$J=[];foreach($g
as$h)$J[$h]=count(get_vals("SHOW TABLES IN ".idf_escape($h)));return$J;}function
table_status($A="",$Ed=false){if($Ed)$H="SELECT TABLE_NAME AS Name, ENGINE AS Engine, CREATE_OPTIONS AS Create_options, TABLES.TABLE_COLLATION AS Collation, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($A!=""?"AND TABLE_NAME = ".q($A):"ORDER BY Name");else$H="SHOW TABLE STATUS".($A!=""?" LIKE ".q(addcslashes($A,"%_\\")):"");$S=[];foreach(get_rows($H)as$K){if($K["Engine"]=="InnoDB")$K["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$K["Comment"]);if(!isset($K["Engine"]))$K["Comment"]="";if($A!="")$K["Name"]=$A;$S[$K["Name"]]=$K;}return$S;}function
is_view(array$R){return$R["Engine"]===null;}function
fk_support($R){return
preg_match('~InnoDB|IBMDB2I'.(Connection::get()->isMinVersion("5.6")?'|NDB':'').'~i',$R["Engine"]);}function
fields($Q){$ng=Connection::get()->isMariaDB();$J=[];foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($Q)." ORDER BY ORDINAL_POSITION")as$K){$k=$K["COLUMN_NAME"];$U=preg_replace('~\s?/\*.+\*/~U',"",$K["COLUMN_TYPE"]);$Ad=$K["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$Ad,$me);preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$U,$Gl);$i=$ng&&$K["COLUMN_DEFAULT"]=="NULL"?null:$K["COLUMN_DEFAULT"];if($i!==null){$tf=preg_match('~(text|json)~',$Gl[1]);if(!$ng&&$tf)$i=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($i));if($ng||$tf){$i=preg_replace_callback("~^'(.*)'$~",function($z){return
stripslashes(str_replace("''","'",$z[1]));},$i);}if(!$ng&&preg_match('~binary~',$Gl[1])&&preg_match('~^0x(\w*)$~',$i,$z))$i=pack("H*",$z[1]);}$oe=$K["GENERATION_EXPRESSION"];if(!$ng)$oe=preg_replace("~(^|,|\()(_\w+)?('.*')($|,|\))~",'\1\3\4',stripslashes($oe));$J[$k]=["field"=>$k,"full_type"=>$U,"type"=>$Gl[1],"length"=>$Gl[2],"unsigned"=>ltrim($Gl[3].$Gl[4]),"default"=>($me?$oe:$i),"null"=>($K["IS_NULLABLE"]=="YES"),"auto_increment"=>($Ad=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$Ad,$Gl)?$Gl[1]:""),"collation"=>$K["COLLATION_NAME"],"privileges"=>array_flip(explode(",",$K["PRIVILEGES"]))+["where"=>1,"order"=>1],"comment"=>$K["COLUMN_COMMENT"],"primary"=>($K["COLUMN_KEY"]=="PRI"),"generated"=>($me[1]=="PERSISTENT"?"STORED":$me[1]),];}return$J;}function
indexes($Q,$e=null){$J=[];foreach(get_rows("SHOW INDEX FROM ".table($Q),$e)as$K){$A=$K["Key_name"];$J[$A]["type"]=($A=="PRIMARY"?"PRIMARY":($K["Index_type"]=="FULLTEXT"?"FULLTEXT":($K["Non_unique"]?(preg_match('~^(SPATIAL|VECTOR)$~',$K["Index_type"])?$K["Index_type"]:"INDEX"):"UNIQUE")));$J[$A]["columns"][]=$K["Column_name"];$J[$A]["lengths"][]=($K["Index_type"]=="SPATIAL"?null:$K["Sub_part"]);$J[$A]["descs"][]=null;$J[$A]["algorithm"]=$K["Index_type"];}return$J;}function
foreign_keys($Q){static$oi='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$J=[];$bc=Connection::get()->getValue("SHOW CREATE TABLE ".table($Q),1);if($bc){$xh=implode("|",Driver::get()->getOnActions());preg_match_all("~CONSTRAINT ($oi) FOREIGN KEY ?\\(((?:$oi,? ?)+)\\) REFERENCES ($oi)(?:\\.($oi))? \\(((?:$oi,? ?)+)\\)(?: ON DELETE ($xh))?(?: ON UPDATE ($xh))?~",$bc,$z,PREG_SET_ORDER);foreach($z
as$y){preg_match_all("~$oi~",$y[2],$lk);preg_match_all("~$oi~",$y[5],$dl);$J[idf_unescape($y[1])]=["db"=>idf_unescape($y[4]!=""?$y[3]:$y[4]),"table"=>idf_unescape($y[4]!=""?$y[4]:$y[3]),"source"=>array_map('AdminNeo\idf_unescape',$lk[0]),"target"=>array_map('AdminNeo\idf_unescape',$dl[0]),"on_delete"=>($y[6]?:"RESTRICT"),"on_update"=>($y[7]?:"RESTRICT"),];}}return$J;}function
backward_keys($Q){$H="SELECT constraint_name, table_schema, table_name, column_name, referenced_column_name
FROM information_schema.key_column_usage
WHERE table_schema = ".q(DB)."
AND referenced_table_schema = ".q(DB)."
AND referenced_table_name = ".q($Q)."
ORDER BY ordinal_position";return
get_rows($H,null,"");}function
view($A){$M=Connection::get()->getValue("SHOW CREATE VIEW ".table($A),1);$jg='(?:[^`\']|`[^`]*`|\'[^\']*\')*';$M=preg_replace("~^$jg\\s+AS\\s+~isU","",$M);return["select"=>format_sql($M)];}function
collations(){$J=[];$H=Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("10.10")?"SELECT CHARACTER_SET_NAME AS Charset, FULL_COLLATION_NAME AS Collation, IS_DEFAULT AS `Default` FROM information_schema.COLLATION_CHARACTER_SET_APPLICABILITY":"SHOW COLLATION";foreach(get_rows($H)as$K){if($K["Default"])$J[$K["Charset"]][-1]=$K["Collation"];else$J[$K["Charset"]][]=$K["Collation"];}ksort($J);foreach($J
as$u=>$X)sort($J[$u]);return$J;}function
information_schema($h){return($h=="information_schema")||(Connection::get()->isMinVersion("5.5")&&$h=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",Connection::get()->getError()));}function
create_database($h,$_b){return(bool)queries("CREATE DATABASE ".idf_escape($h).($_b?" COLLATE ".q($_b):""));}function
drop_databases($g){$J=apply_queries("DROP DATABASE",$g,'AdminNeo\idf_escape');restart_session();set_session("dbs",null);return$J;}function
rename_database($A,$_b){$J=false;if(create_database($A,$_b)){$S=[];$mm=[];foreach(tables_list()as$Q=>$U){if($U=='VIEW')$mm[]=$Q;else$S[]=$Q;}$J=(!$S&&!$mm)||move_tables($S,$mm,$A);drop_databases($J?[DB]:[]);}return$J;}function
auto_increment(){$Na=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$s){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$s["columns"],true)){$Na="";break;}if($s["type"]=="PRIMARY")$Na=" UNIQUE";}}return" AUTO_INCREMENT$Na";}function
alter_table($Q,$A,$l,$Yd,$Ib,$hd,$_b,$Ma,$hi){$Fa=[];foreach($l
as$k){if($k[1]){$i=$k[1][3];if(str_contains($i," GENERATED")){$k[1][3]=Connection::get()->isMariaDB()?"":$k[1][2];$k[1][2]=$i;}$Fa[]=($Q!=""?($k[0]!=""?"CHANGE ".idf_escape($k[0]):"ADD"):" ")." ".implode($k[1]).($Q!=""?$k[2]:"");}else$Fa[]="DROP ".idf_escape($k[0]);}$Fa=array_merge($Fa,$Yd);$uk=($Ib!==null?" COMMENT=".q($Ib):"").($hd?" ENGINE=".q($hd):"").($_b?" COLLATE ".q($_b):"").($Ma!=""?" AUTO_INCREMENT=$Ma":"");if($hi){$ii=[];if($hi["partition_by"]=='RANGE'||$hi["partition_by"]=='LIST'){foreach($hi["partition_names"]as$u=>$X){$Y=$hi["partition_values"][$u];$ii[]="\n  PARTITION ".idf_escape($X)." VALUES ".($hi["partition_by"]=='RANGE'?"LESS THAN":"IN").($Y!=""?" ($Y)":" MAXVALUE");}}$uk
.="\nPARTITION BY {$hi["partition_by"]}({$hi["partition"]})";if($ii)$uk
.=" (".implode(",",$ii)."\n)";elseif($hi["partitions"])$uk
.=" PARTITIONS ".(int)$hi["partitions"];}elseif($hi===null)$uk
.="\nREMOVE PARTITIONING";if($Q=="")return(bool)queries("CREATE TABLE ".table($A)." (\n".implode(",\n",$Fa)."\n)$uk");if($Q!=$A)$Fa[]="RENAME TO ".table($A);if($uk)$Fa[]=ltrim($uk);return!$Fa||queries("ALTER TABLE ".table($Q)."\n".implode(",\n",$Fa));}function
alter_indexes($Q,$Fa){$lb=[];foreach($Fa
as$u=>$X)$lb[]=($X[2]=="DROP"?"\nDROP INDEX ".idf_escape($X[1]):"\nADD $X[0] ".($X[0]=="PRIMARY"?"KEY ":"").($X[1]!=""?idf_escape($X[1])." ":"")."(".implode(", ",$X[2]).")");return(bool)queries("ALTER TABLE ".table($Q).implode(",",$lb));}function
truncate_tables($S){return
apply_queries("TRUNCATE TABLE",$S);}function
drop_views($mm){return(bool)queries("DROP VIEW ".implode(", ",array_map('AdminNeo\table',$mm)));}function
drop_tables($S){return(bool)queries("DROP TABLE ".implode(", ",array_map('AdminNeo\table',$S)));}function
move_tables($S,$mm,$dl){$ij=[];foreach($S
as$Q)$ij[]=table($Q)." TO ".idf_escape($dl).".".table($Q);if(!$ij||queries("RENAME TABLE ".implode(", ",$ij))){$vc=[];foreach($mm
as$Q)$vc[table($Q)]=view($Q);Connection::get()->selectDatabase($dl);$h=idf_escape(DB);foreach($vc
as$A=>$km){if(!queries("CREATE VIEW $A AS ".str_replace(" $h."," ",$km["select"]))||!queries("DROP VIEW $h.$A"))return
false;}return
true;}return
false;}function
copy_tables($S,$mm,$dl){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($S
as$Q){$A=($dl==DB?table("copy_$Q"):idf_escape($dl).".".table($Q));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $A"))||!queries("CREATE TABLE $A LIKE ".table($Q))||!queries("INSERT INTO $A SELECT * FROM ".table($Q)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")))as$K){$Al=$K["Trigger"];if(!queries("CREATE TRIGGER ".($dl==DB?idf_escape("copy_$Al"):idf_escape($dl).".".idf_escape($Al))." $K[Timing] $K[Event] ON $A FOR EACH ROW\n$K[Statement];"))return
false;}}foreach($mm
as$Q){$A=($dl==DB?table("copy_$Q"):idf_escape($dl).".".table($Q));$km=view($Q);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $A"))||!queries("CREATE VIEW $A AS $km[select]"))return
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
format_sql($H){$jg='(?:[^`\']|`[^`]*`|\'[^\']*\')*';$Ef='FROM|WHERE|HAVING|GROUP\s+BY|ORDER\s+BY|(NATURAL\s+)?((LEFT|RIGHT)\s+)?((INNER|OUTER|CROSS)\s+)?JOIN';$H=preg_replace("~($jg)\\s+(AS\\s+SELECT)~isU","$1 AS\nSELECT",$H);$H=preg_replace("~($jg)\\s+($Ef)~isU","$1\n$2",$H);$H=preg_replace("~($jg),~isU","$1,\n  ",$H);return$H;}function
create_sql($Q,$Ma,$_k){$H=Connection::get()->getValue("SHOW CREATE TABLE ".table($Q),1);if(!$Ma)$H=preg_replace('~ AUTO_INCREMENT=\d+~','',$H);return!str_contains($H,"\n")?format_sql($H):$H;}function
truncate_sql($Q){return"TRUNCATE ".table($Q);}function
create_database_sql($lc,$_k=""){$A=idf_escape($lc);$Gb="";if(str_contains($_k,"CREATE")&&($Zb=Connection::get()->getValue("SHOW CREATE DATABASE $A",1))){set_utf8mb4($Zb);if($_k=="DROP+CREATE")$Gb="DROP DATABASE IF EXISTS $A;\n";$Gb
.="$Zb;\n";}return$Gb;}function
use_sql($lc,$_k=""){return"USE ".idf_escape($lc).";\n";}function
trigger_sql($Q){$ok="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($Q,"%_\\")),null,"-- ")as$K)$ok
.="\nCREATE TRIGGER ".idf_escape($K["Trigger"])." $K[Timing] $K[Event] ON ".table($K["Table"])." FOR EACH ROW\n$K[Statement];;\n";return$ok;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$k){if(preg_match("~binary~",$k["type"]))return"HEX(".idf_escape($k["field"]).")";if($k["type"]=="bit")return"BIN(".idf_escape($k["field"])." + 0)";if($k["type"]=="vector")return(Connection::get()->isMariaDB()?"VEC_ToText":"VECTOR_TO_STRING")."(".idf_escape($k["field"]).")";if(preg_match("~geometry|point|linestring|polygon~",$k["type"]))return(Connection::get()->isMinVersion("8")?"ST_":"")."AsWKT(".idf_escape($k["field"]).")";return
null;}function
unconvert_field(array$k,$J){if(preg_match("~binary~",$k["type"]))$J="UNHEX($J)";if($k["type"]=="bit")$J="CONVERT(b$J, UNSIGNED)";if($k["type"]=="vector")$J=(Connection::get()->isMariaDB()?"VEC_FromText":"STRING_TO_VECTOR")."($J)";if(preg_match("~geometry|point|linestring|polygon~",$k["type"])){$Ci=(Connection::get()->isMinVersion("8")?"ST_":"");$J=$Ci."GeomFromText($J, $Ci"."SRID($k[field]))";}return$J;}function
support($Fd){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|event|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(Connection::get()->isMinVersion("8")?'|descidx':'').(Connection::get()->isMinVersion(Connection::get()->isMariaDB()?"10.2.1":"8.0.16")?'|check':'').')$~',$Fd);}function
kill_process($X){return
queries("KILL ".number($X));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return(int)Connection::get()->getValue("SELECT @@max_connections");}}$wi="adminneo-plugins";if(is_dir($wi)){foreach(glob("$wi/*.php")as$n)include_once$n;}function
get_translations($Mf){switch($Mf){case'_template':$d='.JLxY
}Zk:tcv:*(1!nWF]f(8nV,}%S>k22B)M%he-QeY/La~jS%t]=hYe;vw(RyeZD44w@w.7}n%n$uX2"y~`;Ic*H6~?$3IL.V+sso(crrPISnYybMBMbqVL/fhMJqkLw!PpDyeM"Xgog7(^Jxrd%x#XwpFKFZ)ydbyh16)fmJq0;`GhL4X*+Qsf%YTPlU>C%Q)Dp^r=)&n=](@]7dI#nGK:j07.lt%e19G-M_r*C%h6&R="J3
?K5^.93j@^(&],1@T#;IWqfs&>=lDtiJIfXRfbhnz$MvCE8R$*o-/)93#Gd0]3Qg$}f([`4|(F2wZ<;T/=3:9,&[*%0,D((2f58<eNU~.*%BE^#!9_lhr5*^B.D,R>T)T>1[b:fc)!naP?CGDL<@QA!w8hhhh8g[VfOA@n^hw
o^Ch:04d.*f4ABDn3OW4gBsOB5YDumt-CGg4j?df_et7^2GhKSq~;(`Pa&z%-*`q[Oo!_Ymy%-I^aBNWb#VB0<:]#N6g
Tb}W_l^b5BG[B_CyN+K(5@t#WU-=U;V,`.ZNeYhJ-sqAVm?-68(CRZ:TH:vxLbZK{m=iJwD=DFm)6FdkY@CQw_0/6G81-7Yc<ERx=!6(AQ[g]gDlPtry4e@p~&_&?<FN&Nvg9Z^+}6S5rRA>q33ast.k#v:b&I@<T/3(^%b.j+M_SI!rQwn6nY3?YBL`$4i$B1AYo>Z0J)9%^.pSj:tcJF+r)9jF"GTg;pQNhf??Q_sPk.rr.IfLzjh6<,a^Su~D26A+L1%d=rwQe[J)hAUcx!LC<@kW?>G`}B"cRpPLm*+j(_@WC^6aQBhX0BK-F=kTRf]30ZLa%auT>_0Zb6i9xK}xH5fsqnyM=isQ46nSZU9DOQ0om3*[;$tfKobuHiyc4+I$MN2?_>})Hm)B,uMcRM@"XD;1hEq_<`hPR4M!O%?PFD33*[n^`rfDJMS`&G[L0LHWkkJ#/&bP:
T8Fi__|1/i,;a0r6H4Bf}09>zFh^.salkJ",*!;O#Sl$08cL
]
L@2+&$9$SlUjHIk]e9:vB.jfig=I,c_;lzl~ZCaCQk&*plyCPU4s&7238kO5Sq[BK:L;C2Z53!hBdTF,tLixpqu]xt7u(N4a&(9%F$d>eieNCh.[jpk#.c[{;(!uTn.C@}gz26<WU-1Z"IW%UO`L
,-"wDN&';break;case'ar':$d='%c0ATaLp=@90m8$$qoBAPJS7ye3NLRx8iS
)^<}TyJ!m0"NUs^YXfR&8vOkZk/k&MY(#!+f/A

ihEwxHw.^@x[q;hPB!k3Plc?*xKYVu7NwhhSX{RklZ`bwlQ?/ri`Q;!ABLpJ@URIt$`bo2gY1t:a;qMMn"H)]$D,C8m3+fI)E19np.?6dUSin~yPnp$(*i"mh2x/D"e;;aWj)*:FY,8%Lqd3Qe"Vcj-(,^p/?.kubiOtVj(mE5I4Tc4e#O;-t]`N[MtmqU=BxL,AF1uo)wN`?<:XNhy7,Qn#uo,oyUn=,GSH>BMHjgxsxj<^G?OQ?!d,0:`nm2Nhu5n}PAThs4wycC65=&fmM_m:l%w:sP4fa<sCX;!4siv[KjM^4{xcMo6mMVTXxPG~X:v[SF]p_hnuO8m]&2dLY!3ze&)9Cp>52f9HWriyU(1?y71(3!$NgWo,6^="2wuwq[s+DEB5Pvp4##,4#sY+&1ooym15YW[i2/#oW]9?*DRzVL2Sci.l0cL0fN0h9E+:n3;kXk_R)VU3iZhgqg]rTHC3Ek;Oh{+47A&:5Lwk/*S56_%Cs>!ffzKMNLZflmdY2Cv#L7
QpuDO)_Z`N;!LP4DE:@DHk9uT_z,Z(IK3-tNW#b"u=pu9?]WK:XKF0fkbgcsHxiuI%oRxQ_5}+=K{/yL*u.d^bwtP#H80/_2wXb/taaH)ZCN@Qo>!=Z:nnsz!scm`#yM)CrVQGT1;n?,W-WG>@{g:GALr!}qqB5uA%mh6?`SfPT`%p`s^!JR,kR0S((PL[xZ.P5)S0jqkxvtV=Voe5gcoE-m;BI)Z#7`b=7Oc=4Vu!Y2d_H"jx-Zf(<y52UUsE5wAYX,F^tI
*G-#X/$A_&M.jBXFm{k^YlPs_ShT[t)5t.2ddU"T`C*^_2N<dp)C7mwb[ohA,"%}OrZKD=)&+S6NU"%^O)wHH{,l=c3Qol*>,{a,lC!d#H
"QWvf+P`UWlRCn!O&2$K""m"+S<W+Fk5;@PK@j_Bl"tO|S"JBo4!j>_^`AS3lJ=:_Qu_?,nvS&w"qb(;>pDu8X~Eo#I`DY-/_4bLu"PNiZ39a4eWw2>Xa220Si+<E<N?(T$:)NY&w7C^BG{XOk<8Qr<=ATw-i=;#P[ejeM<f44UE^RV;(6w
`xx`jJ[@4&7l3:nc)
j?kLciW)YiTuu/?qgmoXH!s]v7Aq:.#]?$w%
[1=^M{;cU|Y[.M.;B9ZR48?g
h?HWDt~BE@-4>oU,_l%WqTt1uF^ARbDf[43c%x`;DK(pNYY<y1IT<T-Br.=$%<)(@+0.E9{7]P0bH`_-h$o*C#m-ew2W.2PW2Dn^~J&E7_~
W3xHgS]Zg3FxV"kTPx551#e<nGI)ZYJ_b,"<<Gt$5+rug*AFf7mf151#;6<$3e}m*?LZm1d0!
-#&,&/v<PeP+kJ,tN]m]%iBUBaIrjyKTZ8dUdnV<Hgi=Lf<?k$TP82q6W,:H@$HEiwULbhQw<l!nSN*Pnn]MzP"O%=>da,N1kbD?x+*>|0m^3&/iPJNkX(8,UKX.w"-K,k&<AT.,
vG1NA<;Pb<yCf8@,e&Q9xEXae6/Gnw*23/y(Uk?Y*EpW_`_SuEKpn{fy8Uu[[!S|>N/FfRJ5Wvq,[~+|;PQJugrQr2X1&Dj
H!gDdIo{1=,$+
NZ_mdm,;m,0L80Z2
AeM"wSpy_CH$Z(sR61o##D...?xTd-oqDyVQid?Uen1p,m&<P#<P5CgEDPbdGjmDF?V"(C6OnR=*kpCK"%;H>NQOg=9GL]L-UGI9[84)x)mcp%]l5t0+jB^uS<Kvm>pSb;%vN
2C864s4QRhW#]``U
7>M(Dtv-9&U
V7,bC-2s44IL2w=~*`u#Yu:*r2D/DZXtE0tzQk3L=9Fu+5.<7x)#I
?D<Z>|V"1j6qILKaL8pZr5<kSN^$3<KdTG876}k^9~[l+qaWA$CX._P?`^/~Tj3KUF1K>l9LUScgfPHK*4Um),"Q559B_<1L-IMvPnNb9&RSoSESOiIAkbo*?"B~TOqKXD4)AjykM|LQlhMWYHev@9]MY)_son!9jI?)E*d^l4pN6d=&^U2Pb4Oqgxf"k^^TegQ[T?0B7P&EFPRdpB"JA">1Cz*QMcwr[>xW;u/`]P2GX^C><Yfc:uL$JA5uTz:zE:sn]T!oeSUmI!n[KRU)fAjK3WD~c5=o0;dI=Tt!q;6^S<%S]?x6t7@j!PKIKkqNmB"*Xom/N2Y5#-FI`,b:;rcm"7bGYxpO?v]ZB$MU2-Z_4Y7FQ=;xh:v#K3XDihhTARM+(?c=kg_io)=*pfmv:A%KHJo^avq
kP`VBY*9wyhdwS^p<OwnW-v4P<=Y;+91pa)l7{+gCO-=cm2}=Yk"8o:cIyEFi,g{Mtu:u&mHrknoWp,.%<-/mXf9=^,onVq+_07NVPxFAvXxvM#3&E<__d?t5OTe(8W&g<^cn}ct[j/a?sK(2Zyy:h@q#[^Fj6(3Y<L;wO.GZo,oUTj
RntOV!"pCeugS$hNC*,:Q6fN8|6g]gci(HN5^O@|/fB,^KKW71yubTN{ZxqVW;1y>>EQ^~.+=C^Aw~.Qw+j%w1EWK,nT;I&qjC]eq?+AEl1[D%#[_Sb=pJ
)#OLL3}I{?sr],]iNi(ugF9CW!Ll=mvfy",v]/yCr/<FACd>tr2c9k(Idy7mKuKEE!<Ckq6907-?hWl6Co$rdvIAOvj[y4%On>3nv/v4^RB85!{Pbdkh|<+Md*)<X#r;,!B"}lDJMN~fEQItC+Ak1l,d1B=M0#oy?-d/.kO0c@JZlY2NpALyUlV
#viX0)W_3Cr?48wU.,;FI@(%8,z>$gw>uUzr[Tn
mPH
v!xu}ui>3"x[>)$gJdP]3Hns[[hbD`Dg}W<,HYnvj]dor(=ANCNjHA(a98Zm#c`X#;OXyVM4fU&4Fb&?KU]`*kT5%QX9Qsg;:QrZ)lY`qSEx5$)kbfQR/[[DrX)al/F:.RY*V.*Lzl]@#
6eEYJr30X5&vrkM+]VJ=abG%%JL
W>[f~kF3a;sa%5k2cqZSD%312)BGbHeEEF10^
l?fqAS)RY&
1xt$OLIa]n*JrEcgJpNbc"LCAJ(+v;4~Js^XMGv<VZriY(O}_Ou/9bE0G^JjI]<?VIgg%pN!WY"V`fV3Vt:(oY6OglU_k(]u<{Uia*1*bMlqy
i(v
IGV8y#Paq#g6
CKb-lq~>EsQ+cx*s.L8<<0y)_Ya3,fx_bhmL=47w0<#9cD=R6K//lfIN;
hjYgCCrO-*eeYm5T%6bO?*7jpYW,GLc#55p:K([`CJ?sg$*aJUV$iV%:>3VrXh00FMTZ?k~%zLFSLeV]U:5P4v/tEXgs^5Xo&m9[m3zq<c=D9:9o@1E"8cpmLy67c!!<jy4;o?QW,YGFy3!G>k}()Ihoy^Y.5BMor;CGE<duqA`<oEm_eI=R[_ibaL&o0L}Efl?Jqad1!VD5emUsnu&O<`|%U):2g.u4ZsH)OSb4O2}bY*k3%&(UeFH!mFE`A&aXY%Z2~_PGdH)F*5qjCCa_ZWsL2KA?bG%EpvR&yS4aH7QB^>?5sIWG73aN{E&OJ)St*B!M/^)xAE<pYJ2UnhZ4vJYJH$
I*$8)Vac94ZV]|?Tn,bD4hF&LB%wYjA61`XM^L4gD>kOCoppOi8p0>mTSG%X;b
Y+E<]g])#],<#>L9`efGa[VB/SS29+uZAWX%YT.S!w[C`AzX1,5cVu_T<P9bKQVx]K0U6gIRmDBBV.-,%G?U0%"6$G&,PeD69:Z2m;iUx-dc|,0n;>zHmnB%n"kOBUGC~4A["g7lM%Q
^_dmyH-D3<PlG50kPaiDa4FjDllFJ)I3~PGgjh+Xc2

%(bnLM>KgZ5uD6M`vhCKi7WDptWj;E8`Ixt?GrcTbL)w.sj=+wNE`^igIRTKN>v
"vwfZ_H_mH*gNKW?pgI;,0y1(&YIK={F<A#M<O"n``mjIE(/33*4u4ui@Dgr?&e7N$Ddqm>s,(NGv9e=|2,cHk%C.g~N6r3ddEw?j
wN
h1%3F)HDR[_yz!V`.gULJp(TJiO_
i>|Bb/fRH,uh4:g(n:1TPF}^B8dXXF()YVU*/C(>eGX%:h>w
Fm;Y,gcnTmwwvtp6&+M?G;M!Azw_yJoKV!ADX
*Mm:OwwJnlu}6zfapBtW[jSr+W_G*gb@+OOV2YJ,50eVvIp8ipD~e4L9@CDcnE/*<=a&cX`brq_p[L1KCE;@26!3<h4,CkjydBl>_?A9H<@t`|0$Zzw6O|]]bAKosr"ha)P/TY":I
VJ3RE[)LQ=]MkZwSPS5HnD%x5J2f&4`x6pJtx48C/gX5p1>EgQ0Wm:Y/1IMLpFQ|v%Z<D7b6BUpR]*FXbo-vDNC(6r9]4CVQ%GV-J91!b(B=0Mt&aX8{<e]
uuVf_tV!PGNm1I)k/Ph.BcK9bm<J^y/iBZd=d&M"q.MAxXlw5gi,8pZo%c2Hf?^8S2c|f+-4I`BM3arG8U/Apcv4J_L/<kjkhXFq<Uuv=(
P=NXca7`#n/IK:pDxEvX9h=<L!tLJlg&RH[@sm-^o%yIcsAN%N>';break;case'bg':$d='&evLV6l.7,~^!QIfoI-2.w|,dd8#V.f6bNg%Ipj1<Ni(%$!f|wa.M`rvsgI!4Xi"fdV1p.!JWt5A:t;nAxIwtwdb1m8JCt36DHE[mu]A}b5B-](?GS@yfvRjuK#x]uM&^,UdG]|vI8X^>qpk@M{n27ld^ZlWAx9!k]dvHf{2On9b$JFEU
}?>-vvT7uaTZ)^SFc4:/aM][&7E*D.J,j`+
>::qzBZ]x:4NE4P>Al~Y?)f;9),YjU>$$W3-?D$MHU5TBR
_#Cr4l.1.?N)"|B+he4!(piS:bO@CwDhl_n%Xanaz)UqM]wjX)Me6)%Nrj]1%=P%+CDf/b?NSPcImd]-qwnMnU]IiUkxK|MFe)dfa6^S])srL^y/4EK
FixNybb`a-y=v+O9K,tSw!o|m2e21f
=v4w6mXouZ(r6&UvLm89,-TvMi&2O[}3!jM+}9ZM1y^DHkseI[#X;tJ^q5?Z~[`(!?JY_59[Sg^;%+S^g!uU&<MH""h:CZKkc#;9Iq;EO,ZA8ZP+Lq#ju=d:r.<+Bn!A79E>KOEd=!uWiy8"m>?jx?>FFN:?6(^G%p9Pamrr7Dm1I0iA+L#%~vELCTC^Lse9|CoxE:_v`vhko5H=L(vh},R"]3x9p[
n@:p<&@{t(YZJBMzr/SI;%+L%W)&BV?p[Sq|*}ljb<w#3Hv&t&I)aT>tgvP!#1aWr
TIh-1&9ChNg/AFtz$ulHIMpVj*Ww#7XfH"[V#`8MQRF+t4S5Dvm{Q(.v-xjNFVTOw>w$x
2v3w38;e&h>Qpw0QMX^(j;iKw8+1
eIu-1;jS*.YhvhA>~6]3E8t><[7m`pp9_/H!kUxL(YCp9c{!2spM?L]I.^Mv{umwUddH?$gK<w"kq6#U4L9PD)"9_Kvy,H4H0wr;?vSWbW5hgpO`cqN*S:`]q3i4"YqWl3qH7?dsSky<q5W&30kKf7dOr6l@?OVZ<x6(efXU<l4=2jkmK-I!x$ku;s&VK2bW^#3@6$R.qaXa_MZlqT#t.i)Th^TL):1H0(69Dd!m7bc6l8/h[Sbv"`:y2^-nvEZ#-Vuoj
N3)K/e{RKXH5(EJ)0_g/mB1d,hJTJ-m(Qc+jQjDuNWi7.TV-=]/T6;9)i5acJAicmIO@9X^FT-M6JE|Id10SaW=Wk
M
^h0W.0g$->HvmVcmu$7+Q`bdjXy4!AaR24M[zU:+,Kf++Yq5oyqpe`"*C*i9=PGff@6:G^<P2@%fpuTpa(<T$Rj`"c`6bAiLT&{]e+/P:LpQkO1g`_C-P4Iow!_rOonjCyQ*a^s1@i<7V5i!CPmL)1.Q|/z,(ib5GYoNYU03Mu/y2Sam%Qz-u`)%81f$I4j;mwyY
Ck%?Vk:/OWp6";
rlVV`s.Tpm%h6hcs+(`tmYM<t;Yb3V3;S9Q7v5>ki&:>2$s1pj|aUHs/,&R+G3jZj9cJV
*fh7{CV;[hf&DGEMT%9O=@5?fA~(r9N*tR(
y237F)KpDFNOjuB)";:Q;ErR<WmUM6nT+.k605,v~"Gm#c*O7dlD}o5dt^+IeGr6:LA-4OtkR2i?!C~FF:;DX4BtR.gNMKkI`.7pIi2=vWA!YuV>[N1)*JJ;=JY8rN71YxNbcF#e_,zYCG`9
A`>fC]gv1w*..@S>&86f7Im<,E(X6=S`3XS%Ry`I<vi"T=p&GsnV
Oq|/UO%iQ(ih[?4S.;#[{[QM39_kWnHEcokn]<Y%kTic/,"J>B;>L0%u(g
QVU$`3!c=},UyEPMo*a0FmQhdh2L@}5eN:.Hd6Xx$EN(Z4
0u9tyc.s[r_>+S1O<pFNaHh[h:zQtV`Q#PQ76_bs%kyXIB0/@&}dQ_iimxTP`]aPgcCNW7[Te(_!4(n_Sf!xu70<4=fyZ9nAp=n;T>!%<8mFGG&0Lno2WEq[rU^"Kt,VaPEwG&Zo./hP&;`C&k6Dibs3HT1m(eFAv<%8P`sD=bEI==33LjHqE$YxT)jlzp9K9OsY8:3df&~s6SiiadBXzrJ"wg6ut!4UY4[b=.5CsW+x4vST3@"W&k.<C<Yv7xL*&#4"*50oA;m>u%R947ZKGS84~DcfI^Y=95W<4RMP:F
lrF8:PK_gS,e!W4?aZ740#;><k!ar@I$<[%NlzaETUUZy4J$RZdzZ96`h6meEQdASs>3t/h9cK%3>/PE8e2)2^%6pVJ(tAKLTNpW7{/]OE&fF4JTmsH"e-F8Vlna3lC@muV6:y8jAq31k3Td:1g,T-Dx#BT}i/9ij:-E8^P/q>#0e"q"-oG5E$"YKYn^5y*1-=CI*D0Jwhs_,d5x4zW_QJ%/V`3MT|Ko(|+1xWWj!HUDr#F7[g-B2=%bR*&8u
;A;#9x$*rW4]CGn7_?S`f7uSW7"FC^OLQY>5o3XBoLuWKD3ySWk~#3L8+Rd&diZ:I48rrQ//N.L
5=Q=.oM~yQ]-MD3^d
X`9XbV5sSU.mNB9#7Ayi.XaMI_$p=Z_3^ms-U5ivi;j<JxB?@b_xnppgi%%g`QUT_j(ZK}/wVRC)se![Dm_I-UAq,^BJ[pJ[0FP2iMcs5MWj7m_=e+2LiTbs&[_Gd>uB-~.lP@evC9aYXEUdT!!NYFEE5r#{wVn(Z~`+()CCT[U6I**ZVD$Q?~x[$?x=e&pMAl-.RW/UJd){fbG4%F"<SMU(,(uHM@x7AZ3xQ8%.y!b
rKwXfT]a)=OB#jdusWllKA#`9Z
prz^;MFej9Lqd7Plor~>H#)V0`o&w8OOiGj<lLJfw#YDza~]
Ex%$]`%BgLbyI:M.x$f{oYSGB(:%?fDAS!g9EKh8PvC)4
_VTTYlUGJ2#cw76x##.#NA<PfYQFi.L5dgrk:xET4O:su$<cHjr#G_k*"k8:Q$lP&}=A^/cIL1z)4)8!+ARy13DWbPa;^t2n6.@M&ry0kFahu=S`pqhLqJ/v3[*KQ@8k7H:;A+FU]^"1=FW72(7Txyd;U=u+U/;plaiav6B)-%37F6dGIi<hK3)]E7*bp_^]dArm9>#94&<xI7sZ)1OjJF"Evl`f:bneE["2o;%ge6BaZ6@%[HUx>blH>R7],|G@L?*P76[*B#csD_H|xWddve/o>2/7$Hix_!*@8;q;i<:c"0V`!BIDf3^3"RRW_^(zVo<vU[i9LZB
w;2RU<U)@0#+[J_rb"FekB:V$sJ2)v915;oWtadk?sNgmqMWrzd(G*OMtlifbkD&8*LW>xs?lx(gChuGm-Xg%J:pqgfDeO+1Cpb2.2T+Y,X=Ms);C*yKYeKkVOOKp[:y_rF./2/(+9dWhCD2?6ZC-WkX^"AwCr)eF;@4h8a
uoT<;P^"Txm@eNBcdiY3*Ig[n06*`k<xnnqzbl+3Yds%2K4Jcm9>q~PZQlb;;i%A^A$s4N<_%(@y6js5hMlP9G.iv`8V.[pEli.3$4qa7QV(vYQBs}t*#|V;(X`"#Uo1*2N_avFDY|afJB/cS{p,i#8zv;74_PfiP%w4K9UY:rTiL80@&S[]etZ*DlI!ppB@/C9H`n8^h_I3E|g)h0H6R1w3"GY0m9IMen<wnQJ-6S(:W=T177^X%3(^nuc.-V=idkp4YMQz(,yhGsjBLgA#i&A2]OsOPxFJ<?E!a1Wu_Q&O3:M=]9uOHd^]bCCMSGP}.!uqj]F.6@UQCnW!;9/r>h($*8@t
3>+uAQ2HH_O?FkoD,e]6{U4b0f4+o^|PSP;$`nWui8`4Ot%DP"1w)afKSL#^*%$RQ0lfj*dv;g+a58bwdN%^[jWDz?{:RmvfVk-Ut2D
$@5B9.eAxJMq]6:CCoq7G*)"tFnaMbNjfJ?28R`GOD79iM>/L8xAAWCe@VE7._Wd%0y:(Th3ulT[kd0V=j$fPic:|yqQle`?z1a?vMh5Ga(109QmXk3l^i_fNmot21@y,b{llVM@x+C#{g?>f6P;BIYnf)qdU$.U.E2"j3,W0Eao_e_$1+)XWaZ)D]hfG0`E/RwD$<5cWMcCzTNnB%Zh|]y3hYY8sg%=G^?a)bs:ybWMQDAU]@R:Un|guCbOO6uhb$Z1"T9cRu*^WVtnbV+4D%UJWTc;iY4S,mtUt4nGeWlc$TCq5Z;Ij%hmA<;*-fEkxMse+ThwxNEH{fH*#$G6v$w6EC-d)tTMHkascjjE0SY8Ps;.;[uWUmb>w`]Cn/FJgg~oN@^kit9/*/X_Q,wr8Q(J|Tqp(dnJ2f=&tO/`|H"(WchmN#J
GR<58az1$b7E2xIavFFSPy0<Imoi`qL
{47NmRObF5:uK*H#,j]V:_-h2%eEaSoZ$Km@e)diz<hu736,f/_.0"D/vd9G7(eiuP/R$[|m.ybavju;G:#X_v>PYwF;:11o=E@^tE:Oxj{S<RKdPBZOL>!t_ZqjN7O,(+[!}FD`"<<-=%sWp+yi6ewJJ.#E7cP5cR"S[U}/-P}]&[z3
?f
$ImUz>agD[LjEJ
lJg1w%RoZ2"4_sl|3lo(*Ar"C~s_xr+Vq#/
w/"Bm#.-HO$W4H?1V`EIcV!MmzpTqtct7w?$Kw!ALB[4-1RN[?q~XPy%g9(Zc[AP*qm(l<I}6)tnn$@$t=tVgRZm2}ZFg(ncl?PK^-TzMUAS9NhzNRkfv]D+i&GM2Ukry&`/d}!i%Yc$OqM},7x,NAo3bZK)yHZH*?r[#of;Yz>AhW@:e4FcvZO4Xq)z8{M^4N>o0VfM&,$Ny.VdK"[|]QZ+;z_dlbvc9`S3`i._JCPMV]PGDEWODLK/d08ZnSRG2Ko>x<Q-(Ei~U%efSh]HheWMaesVdAQ%<kPo@h,c^ZSA0EeS6{1Pj:+e,49uH+i="+V%lv[*98qc
y!]8?:>.Kn)72uHGKuz3qPEp6d4dH]o*B,
Ta@sm4^=Vzkhry
Lu
@nJg7-bwc2VJ=6
aKze.r[2o2DRAL[a{[eAl!G_!2cWZ.pt4`CHB1Qw6RL&-G@mt&pq&x;,(-MaU/QeBs=P*+Ez)Rb';break;case'bn':$d='#hWWNaLpK?G1/O*.n(qtoI<g#E<$)XL8~[[jxQ-&9&T/{5Q#7oYv("tC+C>PiCC&VT([/
"g:?j)K-=C&P+#tuEK&hja
X6<Z$#=op:=,T^iJ[Tly]*vP)Ls2L<lA6}vAKlqBcTq?M"hsDchs>mi?x[b`M~c4!M+N+43Xmvwy((MNd%5Yx$xy!0hyHQOmAw1[+dP]D%n!F8a27]LAq@En5/SF.&2ulKoH-AxeM/GjwmqRwDgk2"V5yisnL7suf.fauw5+xj"(3ynp%A*H%HN&Wo7Dye,
06#?Riu?-fY/KTq!B(v}0-2WNTj?_DSlw0iQ=NyzmZB^@-M$5vr.%(8]]|A_ba<|]jOm@,M`aB^Ss4w2<.c
cTXGO6fLc
^K],s1w_+TG6EAydc#yEw>7vh3tSLLG>PJyFM/V8z$xc]`q&M`c`rNE.DkWq
sVkOO),:nuoP]9CWI6LPD-LFRIwUNe~,nR5nPHVRuq-O;W
tae<ns,bnFAZX-57l>0Q-7E4PtAeaXsBoPDXKk-1&TA/apmj@0wCh4#(h"$So!S#?vol274?d9wl:*LmLS.pEAu`QB.fO^IW..j;L^N-")@B%]cQ
P]>A~[
RCf.+c-Wsz@BS{9Tw|""k~"gKe>F%9^f*mXUMqLxf^&D_B5mGh)D7cd{VIFh1WK$!~+]5[.v*:4D7`"_b;cd
w-1SX3Y]}^Hc!,!?bJ:Nm%zid:cqd"$DyJo(!AL
l]|0`0{,UIw/.$w!|$<GJvu*>-=<>0&_5H`
K
/u;/)<WSJD?*/E#o/xcuw`TMs:P18AIp<9nH6wzt~!=xEA/Q
c=Qc){h3"
qx,o%,JTos["H
]u_r2o#F3L@`gr]KSu4;=q9abJMBbCn7drK[Y"m}k$A_`=K=>rCiRY6R#xmE;O#y$|je,F(g[XYsOn?lr>3(:V
H5^8J!,gl%FqG[UOy:QC+GwW1%8%~Dj5IA|=-kiBwg*fQT-8>?T
ld*:&LF*OI8HN6h>`^iXkTt_RV%1L<%KeWrLauKlgvBoG-09jDM]Hh>F$z&v0H3O;hAVSB+09:hu0
|n,7Td+mi0S!C"if|pm!r.dHoj`Gevm4|_)U.oTx~$u[t84
o
2]gvCv{?d!f*e;/v6u2Y(@Lc(9,$3I*C`567{QYZetsNbB1&NA~J2ARtD`
j<D~<}TjeME[G)r@*Kp-xtASV|pX,6:MRvsg9&3s$g2BsqR@Ec:}
>hrmR!JvGdzu^+V`-g9+T>NJX8tv0&)`jEU/ZfWL*1M%fJt9v,|#_n.=x6&Z,G$:,SrexQ%K*JlZw1Ao?7;2G[o44>dJ[RZ4WgG?7/BNdU+r]ak8RbNcF,??;H/vdf=eOFd!&UBf~j|K?
`?{;Q9gJb-u.o!T"}=O&_:vExx!b,mEi.
I4.le%l
3Z+yJ)N36L8^>_n14wL>O_6a*_5GfnK)T%f]7ed^~p4o{ZjnIXSR_JG3)WS$UNct2g2:5;)3
RT#FhiM{FIpeQ&(iK*-f>I&5%dj&"]h|,DU`9KMa,JQHp[_|dFazS@&:ulbvS0-,bFJilj+ST@QuSpEra5U*qzm%(i#QZOgpQ"=KX%I2gqA~PmI&-"dtXK>BX+g~UE?DV)1ZQu90AXIXw)LXTohm*/HIpG]"5AqzBT:o@4[>)C>l0#(Tl)^R2]W;w}^Z:C,{Wr
AZ4+8Cbi"6"g3;J2Tg/VtDD7VvWYBh>-gG!0jAev7)}GB3*3hD5#183FfCsO>i;K?0^7B7McX_$w)Mod*)(k&O4H982p2;/Pu*l^4;$dHf_*}B`dh!IF;#p_Fo>Z{&"ZF7YFsN1i<xZ=v%PC,2Ty)A)N:dV+V.DVqN6@j=bLX3K4GV|R;O/=?U$DwOHZr=8.EXH+9Yqi6ii0<l,.%dH!_:kOH3wIqEjV~g9v5v5Fjn[u#!xY,[aS(szdfE}e3Xj"Jh[*sj<ygZns>P]qUa-opC,^fQCU>[
&R(Rt7$^dR2"6?Hw8U)M4KYc66Cn&lu4t8X82QOYWFuL&fhq#gaxexem!Yad;6VND%,l:X[2&.0M=T/eh,=WW$KbYvQ4k8m?*QdTN6rWs]`;Nf1_^y@Nf)n!!u]O2b^_?E?uWQtzaM?7teCkBZUR
%:<S)ON*!d}Amfn]_u8D)M*1/At*Qc%Jc3Twu)8;J@+s6P[Zp!PjxC~^WLT:|!<2FP!8?Lg_e%RV9FZ#hR.1TB(,UCl&2>X_aCiiE"`lpW$*;jC#5D}lFeSk`lb*S9K6>iMKFXnC8>>Ej]olm8En!!uKM,1xdcJY,cm*=U&qd&eIlp*2
Hm8]bRhu?6o>0M(5#BvZ$cto?H^R-tKG!zND.(UI,R5k;:)FoJgUptle6B86w2SvbVx(,~GG#iwAAt
v&|O3hXt#;7Z5^;1B/5Z|P3SWJ*p$Jk88:;ZS=XH7$q:Xe9BFHk?yF1(:D`ic]~R4sZf
@Q$"m=@p3j9{6D9B!.1?FsD
o2p&y-+ag]JL!tDT
Mdq**WbZef^UY8aesg.FM$o[1LD*TuEj|[R!Wd+7RSzO2"WwYH}!y,.?zwA"L85&8)_79rlA9riU@>
cP>Sn*?8?
N|B&B^5n*5v8L2GCF_SRat$Jd|Z+eTM;Cmg>[H7r#jV+$)0KfgM;r1Z!,O5$w
TbH#w~qX<
H_N;53y[VD9$89A96T4_50@LQ/WN2rLXqU,`]XD5bgf#0q4gFP&}RS?&AQ-l$S-,As4m^
S/qbobbayI$R`eWBm)5J])UaCiF%lslj$s4B/hsef_^dssUv6rv38+mcr]uR+6`V#:<W/EATPhuzGemL&b)B?vEUCF->UjUu[T#VO-DCt|#1q0G>gYt`HH4^yy2|xAJ]gv"wFiI3k|Yx!WBXWOH^G}-SmD5j:JfKspL9$%.FFxlQat3Q<lY!NmtMyw,]+IcM8"7QI&%C
[hDNWhO&;:fI:Ivx-)X>&^A>7
BIETBo?C*a.*O1.`++#sARwvLwv4_VD@+>^.@c+JW/phTQ<8-Q:,5E^#w+SNrbXe;Ks;`
<IT1!1&-qk.kvdA4YSrS&!W&3J5U
X^v
K7]xHi!:]}kc*s3DJ_#EF>s);H8Aj/A5.qsMl<;E$<M)S;rS
,u!Eg`?%m^2WiRG&e;!$5#^`&Q(?6KUJ>E](^PWeNmGx&-kK]g.:80TfHQ
*|*N4@Q%:Ve?;|Csh{x%$/GRxE$t/<seoqM
W4bXjnfn?]ni[y8q6/2c?E7yHD8D@k/UV{:Hs=B=7MfqD`_![mTI)Swfus+UY~=p+z?.1kr0S/*?0JAy5|/ry@6gU$),;J_rDH)!YJ8Vv88G]ACUa#.?BaVBK#s:W7n#%Z/CH0b[ly)(Ss[`;pU@F~s]dK9LuN=$[8YObfn;#UfQ6yALi]++fvBv(#08d.PPGkt
9E/4ERD-Zm0.wS)>=YX[(l4*^s>=?cSn:]TJ[i%@/#%f8*&T!&<5ZWX:G^BCLddrZys_VS<sK?a9ZITD6TieLNa:O|l&UxQ(P"66ap<sO]d(cJ)Qbxfu$nTdH,"N:IK4F+O;`1J*$CCHF"`v`T3+.R?zSIXpXDL!G0
/t,vQ4nZi`1$u0P?G[5CdeeTO)Fef
Zn
wN/-YS^,Y]pUwNRyd]*>B%?3oF[i6q!|G&"s4h
=ua$[%UT"0f9vx@e/V<"Ft54^<zN@J*WoD(e?ejE?h,
P3l$I8AHzA`$iLo0M._da?Sir
8m>PDJ_dPmj-S4qRwZd-D^n0/E;mOsvoz/K2X^4yYA6KCFdpPYa<rN0@V/yx4r[tK,$#Lu@D.g%eM&9l;5Nq}yPBl0NDJv@:_ZEFg&~yq-22@&lH,4[8hZ7^i+%m.MbEX/g;KVN8Xxd#b+c/.T>P9#F2SPo8!p$;n4S*~`]Eq`DTEG>d_h@@`_+
C3
KmfFo+caDdk~Ukr>o[41k
#w
WZ*jojwbk%"4O>AB0Y/4];v[_j2I&,&b6c.RW0k"vLQkAo_Trj:CMIE=&%TJxP"<rt=sXqQKZ-YN1,5x8^D=Yr(F#
{-Xh~J#ACX@gkajR~/ED>h_@7`^#Qn%Ge`NLTwZF5Oe1)x5U6lUckKz#EWN%Y`32*gT;QF@,y&J$<@NM+a7dH-+BD$%7qDG,,q5h68nmQwjm|faQk*Y`hR8%A0_X9CJjk(;1d01dq)y.qv@.#gy
b$QaDCK,*Y~=80r$*?o<]H)fWa1#;JIUgr.<#9gTONaN;;OTW&37UuP`!-K)DPmV;q02Ds@AU6$&iSiPMecge0%B[j~CE@L"Fa6al8P]ODld][zI}f%i<2MAqGz/$>mmY)r&O6HDSpL
>bt^EH;<,bKQt07`nq=:@ci$]]7PnI:a!9|$lQ|r1.VF.CqH8_n1I,K%#0_(XtgVvdfa&vjF;fcuOQv-*kHj[1e1xro%-F@^,Sd%}4ZslS
=euK`As4[.Ag$L^*cFRk$}q-jY)9l35dd(2IQY(it2)gr{1"X9LT2dvoKBLI3?BU;as

@q&ES/KEYQETbN8fH(Oe(aw^.ee1mZ6Em]"MxS9KvbE$>srA#!dQ:X3=KBQUN.ACUtxlaX(jOlsIy5ztGI~N3<]
qGpb(QzUqJkm->Q]ESr9OY>1$rwsqBF,/SK7P]zy
28N
ouo;mxS+KtGd!0hw^!bY/RngqR$FqW=ra~+|IfTi>|+53=BStt@{Q#)rlu7:dZFSvPLbr2_+/FqlQf@b`74WTDX:4`7Eyj]i`3C%c{."t|%bsD_V/#S$juLC^"(>=C/b7b2JGXC[rrqVuR^m@7N:7
)*)#wZR7ni=y&P@Z><3Tr+6]WM)Dt}e$g4i>cGFM<qPxTj##w^B+ATm{`!peEs
qII^SSf0`9bjV,?ApSX.9^Ev$8G,o%5.m3X5cV
xFF5]@W;ay@aaQ3ZXWM16cc~Z}<xB%:TC7
hgEgNYE>.g"Vx:hwxby6847bV^=l=vq,{^W8"-W23?5#7i`aJ8qbrobN
(a[rQ8M~V[I8o<kpnQC>odO+VbT5po0S@>)9kQ@d4]r:;lF9C{!jm^>/tYhY#Gd[)B$41G6[%[W,uR]?.fg/$+b,MV2!0HFdLr=dN$.0/V:W#+T27]lWb$Hw5^2MIAZ#2xYoH}jZ9I>m6TC$;/`[7^<*S)H4WRB".5_8_6$N)ko(o_';break;case'bs':$d=',ZuALaLZ;2M0l8$*Cko5bPZpNH4G._.`PT*Ssl9El<fr6tw<qAl!S1mqT32s8vOyi/fK;mJ?2rH:kd7q5t5B(al],m8hcpD3<!By?@$q]ID`UrEZoQy=/oHB)uc0@]2K7Z;]&ryIja&g^NZ-{$ywx#g<EwtZe(`Z$x}kYySn^Syyc6+`^gc4Iu,^/,JO[0*)}LSF*awyV<DR(%/bOw,PfAQfcD?2A]@v][ROt/~:#G~d^VZc{!ZklnWw5nnM0xcuyK{V6_Ru),K$Zd`x"Lg$CC=A>sfiQuS6w0=8`wp^.][M~+W@3yCY6h5WjSe=,wc^
>Y3g_;.X5A3F6t(F>)Bv@UaxTjH&qJ#uSm/h_Y`v(t>3hYaz@aX7G_d|W2rm6W`RpLwOCtw6-@8P6=yhR&b_d+,#T"LF!n]FS=T,LIQEt3GjcYXG*ATrEAu|0R3C>irS*},s?(4Z8x@.FUE*vAD5?ExCXrk1j!;]IJFgV3>#gmivYnT!To--*E5R5aeynIOT.(q$i6Q%7Uew,,PEw+4/X^Tb7wN&m^/6%K@V9FNU[nUSU*+QMEbWGu[]Y2.k!1n`KOgis=Y|R6pK(I
qku3tT4%/?Ut.2g
und:8M-B?;1+GZ-`]$m=?chj@E:cT_
p*yOg.vJLjb<AK8Gl<lqDfWXKnvT7oEY0@v<S~KA6rEr5(dDW$4ByIOQ`urHW7^/xK-bKk&B)Wtm`td&RjD5>XPY;r=o]ySdCll3vII[^@[BWtneWzBft2)Sp[Qtp)WRw(Vt#S45dK"Q3DnMPr6:#(g_Mb5f=nVxU5G2RWL.=LwU`Sq.!RV@I>KL9c*fh4ll0k[LZonyIzJL![JBEG&(>ls;,wXV:~
0lg<xd#*cvEf=[aa@?!-[?!]-HJ=tnDVY=3ttEPvX_N1Voo#Ea)]9>KO(pf"]fl"n+9iOpdf5jCv^!pUW^>XcQJ#<Z#**!`E8Y=e-*jx;r8k>m|0@c7gc;C8WNJD!GSFK^}%lYhgks"Pd)RZ{K.j)@SUDy/n7f*o"=:).Xi2TH0R=gS]:aR^2d}mc75wla)1Ume)j+&:J%oK&wl:,d
/vVDg=^z^iHq$sH_k^CK@l5k

xRM?Z<b?!7B$IA^-kIicKi5.14b}4Z5qIm5+aOrg/h^tfgr,2V7a&mfXRrCE9uMHKZqhns>IG=EB$G0*khu;C!73muPTPy)W&XDyc3sdQRM?7-EdT0(fK~7YCvq^c:pc*jMi;?V!!|:6>mz!PU9Z!HG|]1sQYvWk/mVuCOA#xoi2@n00>$]k6brDlOqI$V4c695oE^:!B_QEYf_>ZOB^BQGoxqF?+quW9pfuQ(Z9?j4z=Rrrvp1wfJcUdU-bK_G%HsMZ:f@zT-IVCmX:Q&_l(zUg,D`!D3^7jfk[LZ%}v)(!$~*<gMXlsD2y1gVWgE^v1bR3r{,#:yWR)aPND0x"94P}85nr7prL9z,KE{kATJ0CFx/8VImYDwk9^n!2=a7$K!5?N_6:6r8WwH0/P{&0CzDNnK3<daN>o&Wr9=O(r:>8uL-2/Oy9o"uFnKCDdg#d0P-%W2x5

QY5Dxb8al@eZf@M(2x8~UmC$ojyBhNGD&.Qa+})Y=iq6]hE
k<g1:%(3ffY^&B"-DyUzds$peRrTk6^vii,GT?^+1_S8s`]>Ag1|hI$ze8HLM&Z}->YCgCHr]^6z*|@_=
Q&;!@4r"?:hI;Y4
`o
&!Zo+"<DE:5W#[51*u1D9W4P,cK[bfj^A@i={"E
{@S9EvAN:d~^|josb8,F`QbI{mWobe2x%jd=pv:?9?~vyH=vt),0*w8ZJ;iMtK(0lwUA{lExkO)`PTtX-@n0wAzo&=[+79_vi$;$H[vk9-G47qxB2d9MvZK3.O#1+#M5K=!L%S`V2+mJLMYp8h
PxQ31*We8-egF46dQ7/yQp@Vd}wUN2r$Q}CM>;_jG*-Q#M2t`e*hJx(1;k_YELI6W.;+EH(Hlh$3W$
nA.=j$2Sp,)?s6k,R/s3/T?V0GtOB%Jxz)NK!kYk514b*g<91T#*)eXs6q/FVslhfd~u?Rb?Ue^ofH$u{IKVx
5%~4SD(csd6fma0$rS^F`TSPzO5`f?~dbnNNn;H_p[+[FL-,%(dJJ34eV+1Pg]pAn%!%C*=:.$N5fNQ2un/):[r(Epl=*@pmc7GCR@S4Hyr?o0=%phn*Q"awbnAex,DYwbh#tN)7L.CxR0pfcd0Rx>xlnZ^KEZZen3l*CLK(!r^S@UE]]kMqzSywOw.8qK"9dP#hqe[o{%U;O;.<
HsAh@*(]A$c|%hy37">L]WxC/,Tf
h_N&FlpH}WyV*:Ai@iRpc1$i[
JLQ^L5ukg#:gor~?3uG8&#fHZ1_W+TOC6[L&ZuCIj+tc_.*
cB";$&u,n:4@Mp.HL,DQ/C=5RPu(vg%
^/04xPl&2"`gcZM@,/%`{U_0K;6J6X7u^;$P*1UjD1?`^XE;,H~oD
rdqS*9R?,!Z9te|F$TR#T!xJm1~=Lxa=!*?_Os@Om"+Ic:JpoM|DIdK]xAxK%A;Z.@GkA]u58S-aWE0
7(Hy`9#jUG"jld9[=9oD71FT00pZb<i@J
1C/;msy,#pV(!I%Zo7.p>a&(Cf/
5.nM99HLn+w3ye{!j3,E43QV0,{o]QgLIQUJJ2YWv/K>xMdSd.obZmq%nJ~AaeycgmQ"dojj3/B)*KJ9$W-4Ie4QD.o8l$Ps5"8-:iHyvg_;3<@@5Vck0J.N]j_]wF#Bi-PWawqby18<.WbLZoNsH8%DRrF<CA:`[*2%-K0wSI4Sz2WJ(
rjHa,d>l!eb+DATW!IEm
9C/8av<xfTCGe}y1>JcoQ=M=hO(t*%i+46?~C3oHuZYraF:S_p!ykjjQ-uAh)q>;Q^(S?MmYx54D3)6DB0A.%Ok!1$Tz<F1A<LaN-dk;P_EaU`x<E=y(o,v!0Hc<8g4i-x"?#:sr&!MWOG"P80gqpQtK&cs<f7y?UZ:IJ;yUF!<bZ
veAAMm[eO&rw
#J=kW_X5W
r?[$nH?+frGa$r_C6C|Dmq
,D`?F}&YovO4q0nj*TnK<)jI!:`RVesNfQCTjqma%dM/#)k1WOi[JU&/b?P"ET)TUGj2R:;@vjes_y<X.gCoV(q:4Nn:
ldnH"vs1vlK;6OC$+:=Q;kfg$nn%a<*^3]5PjIa8,6+A+>B8yA/Aq?D(S.}+HIF+s]M:u9S`6dU,FN-kt?sIf!^siw6g0^MX*PIfwLF#olQ"nq_Ia2mOIy5k
q7ll6M@]$6C^g0t#,B
kG-fcla#KFnJHobGj^oln9QEDYScxk1]E7vE*o~[kjwpi0cymbd@a966U]L)!@(eLyf4qCzmyv$Yw6UX~m@fDJ=P<rY34CrgT#bP*f
PALf,5vV_<shIFl+jqQ@6m9-l4`HbTcliF.
T*rFalFU8nl)R^TL1D9R]wN,W<5&R[gw=f,9ALH$<r6pp2e1J%a`B6QI&>&_)2V-X/m-U}^nqA4a]a"F,UCW4/KagDv
hmlqUhf0jrtH:*D]n=9jUj_bE5[E^=D(n?Hdl/DuK2.;vSdcm%roI>UB;KqEA#]Rh
IuM6vQAYPdc~Rq<m(sLW=}1KLO,PCYg/C@"u+Z&z2Yf!S?S>I&Ji=Vg
$WU7S63Z
3>W
5D6
@S|ve,OL(bIJ,bTtHupQ&c41i1{^@XBlcl$Bn&7W+ntxlQBTfKtLRb!i8`3wi3u)*^zGC7Hg;"MTot_e2LqLR@zVYQq90P`R&eAjd">Dk"oHW3LWu]uGDkbXY8ij5pak:3rfMCQgB`8umb2U|xZq|Oc0K]Z+&Odv%)sB^>N##/gl#Uk!DH$xaPv4/T:_pY[=u/Nrh(daL3m$
?5BL=)9e10e|*$jT!w<g:aOM"v
!-EnaiWy+:7$
-t]Pob>A8hYg=4lxnT;o+y32U/
_g#*?#.H|9@p7TU`0/VTSWWpLO%"ID{nw`1s7S*L8;U.kW,1b,Y]DKRG-G}>$i*tTn)SM(o`H,Vu=I|8%)Gd
y^/nVtG"*1H2AJcK
~.-E{=Xc1Ex=HJXE-<%VU.v(bn+O_.B-9(v[b=~!HK_Iz^=1H/"=hNCXO!,:M2Iy_M5HuD&+$<NFL?)cF;cL%+E>
+xH8QM`!E{pxZT."o];^o>SMcdo:bMcX@yso86_pdDlG71C?0xNrOVlNN/+$MoncYz!sK?n(i@JwfqS_(w,Pex2ahH;OBr
dbgWIc6;icswS0gr:t/%|II(iBydUnpj~uL#98!q4pxtyM@lTo%)t`SAQCkO-j1<9ZpG.?WFL&E&UC&v`4_q3ZokwI+dvP&OJ`Dj4%r8?$T*r){KR>Jbq+T:.15Va*4Nts??Du%_hcGT4+VH847pE/:4|Vf%t`k8{sB(:2-y9p,[^=aR_?8<ZpIGv6$n),~ujo)';break;case'ca':$d='&]^<-csD),~?:"$%8FgF-h3&TSZ_]VN,2]^=|9WDt32IE
~*]^?MK_Lv"MStL)uS
Z2,I8tI|7`)tKncP.wt54`=uytmVWdbXJyn{W[us6aO=oG*laIEq[nkWbghfNY
./vL7,qd
Hb9#H:l<]TQ@wns??.VwQKv-F-fv)P
f^RGFFWIzbc%]r|0e"JuTCqZI?2O9=LglEB/.Rc9Ieh;`XH[~TWK4S8-I1b1l-a(rX)g<n4&9]iWx?C;;R[^f.Q/f1
-_FILG^~wjY8n1HCtUhl1$A6q)IcW|/Lqhtt0wfSB$w_("H!kN>"cgPDv.e"eGlgLeW|[j<~@pFO*s&fq?ueTNlol))(YZYZ6;)%v"e)ri`*D
E@ggJ2b6G/cn
Bf`-&R4emg*Lh7&0AD|!
*a6wo|aH@r6{(@wGy6`rMH42l_5P/1%VE5#Ob<TRWXZZpf[X+&B$Z`]s-en[TL%lQr+8X.5bZ?S;%-X`
vVYHEIB>C;!0b.LBw,Mg#,8kOf+.9)qX,)[`?B<PAFfC{3-I?g@wdib?l5hp!4(%%@ukYA/4AQ;.qOdCL5xUQglwfHijZJsBE6b1Tb>@ChP=nLDX_&H11b1.`-dXY<*A);X)uT|.uTM4IGvf8=&_Fh-J|"4,!<+Mu<;wrt`Hq:3.0as>$P~Sf)nrDV8hx65`)UTA<:(MZ9ta9P1XqZvTEvsGQW">`%%?!rh;i?;^puum6E$s6`:vSZ2Y#b,ymP3Sh?BvI05$Y/y@HoLOqBOf*AeoIY:q3^<XU@D_=Kry2x,IX-y.!3*4|n+bbOg32d{;E4y"h1i1n$A4,*~7Cps9QnFLAnk-Z4lcsz!CU?8%74N>6bGvXbyb{0)q,82t8xhM6C#U;x`8zO*rwv@8|l2-XgG
sJD6~1Ys^P%IIq_HyTBT~Ez9iQ8=a+ON[?FS16p"33&r/xGb?%wPuLqQ<UG[t*cZ2/W%QZ@,dAQDT^hmFkdJFfpv8
9S5yRLtz)gys(=5*LX~w(MKe&vP-8s
5V2y+<kB<ZALt!-,A14t1n,V=e0j<&0^cJUgQt#Hh|K"(:S-EDi]8G(gU4RJ3&H>fjm&8UD0$"IgjC"{>s!18Qu$6k,q*X.fPAVncrE@>,RK&DsAw2eafycP,QR9p=hPvshcKUxp`_!qD0bSRWA`idt7IW7=`(hhgX+Ph:!01z/0eN8D7|ceN}DfTr3Vri)+*HWsGl,fN@MGj*EZ&bW&(K9#3Yf^V}I$7}hyvH<<vId$y-P}NV^5/y,
TMcNcs2cuQpkr2_*/3VjV3H)]D
CsR>@?S05<tgz
LNI-bDtDv=U`OOWu/STwt_Bd,_t69lh7.b$DgKQB(_^Bf?M=}x|byn`0n:|Y@e[EV3I!#^x@zFfF75z]flO,/N{q,5bl,q_:NowX7Qu>
-p!aff:y
6DNXL3A^36h(h#bi+W=YD[wTh_>m]^cM|hwlmnX&lPpUSBOc}U2WJ]wu5oG(aO|I]MO)FPDIGHwa]mcQ>(4r!TI&+XN/R3ds&h:u5?sXaG"GasmFkGV;J3t4ynX#cNB)8@X8S!]VUg}yOE`#9x/#Wa:VD3sqxVPXP.$M@$lEMoV$ZONtbU}/F/vo_Q?^b/W@YZBofG^:W*-+t=:`MP@I/-;c[td]GY_#(&y.xN|Po;Pkg#jIqE7?u[^AEOP=bv_cn7oZHW-r<9W^&[uT
:}Q/tZ":A`J6UI.^ONT
D6nDK*ZN
cUOr/4yo*+tWDneQF9J
%
6>QQ2w9+E:hgv!Oe)POY93)_w7S_kOY!vpdN)
E).I(r|s*Uve8;`+{k_>W[P_gXwT$JpmrwjbMH%Z9MpB
+"KtJ"3#MWu`oDE]glHjArFYx)fqZtM.$60!FG?{vIp#:nT!tNWq</a{2tWnUMZ9h>XV3{-]ilHJZOxHL&)s(#ozloI
3w*[PmH]LX+tD!eE))i,)Ar+UMbqkA/R$)?^+6.|c^2x1LKMFz7!GXmt({)XjZh:=x*`w+$^I>C}Q[!nu{$Ff5.xv|I/pyG2>uZw
s1~s-9J!fnQfoHxYE*ugC04k<)s
aXvNd%j!SrQ7FMd.sUaWBj^MM4~Tq
Q`>DLH0gPt=/2LA_&1zx,244BAh/uU8jqNHhq
UA,z$KRC%Qd:?j$?P:yFzdQ[tL46;C+YdR$YyAUqx+kf+D6D|]jh0Di$Z0x)5&kJ)sqG"hQ;yuHw3VVKnkVtTu=i-Civ4J#
+1EBhOkMR4-:}$jy7vIO9[0RZQR<A2d<@1_2GUMtqJ(n4jkSDi<MW[__Er4V/H`h2Iy$2lM$WYz[F?B?l$h)d.&uSby8O)&%;2qluPh]j/F>xAqI$_:RdD!6GwS+9g
ukB1Q>o#yU^1IlvYR"soU2i1aSVP-/NQ(_DOaA5LQ7W:Ag1J)-XB"BN`R}fGPOW{/}vhtp=<S0T8s_Bh.m$!y}7G%MIa>psD:wi_`FIc4Dx]`++bU,Dz5FpMU)27WVB,O7MG?I,o]8#gOA^<
9_AbzE*c[8-n403"d0ASMbbpN?P#T`
:sstXn:sfs;TsQ?O9@Sf))msD|nzdT1YrsVE=JXBFG;?T0gry^VZZ$")"8CEh<=Dh,8vdF"-"OG*:<k@X-r*><>b;a2&$SXN6g98np=tUoi@T.e8ML$6pnLe^_6^]u87yaighT@*04Y!eM80)EWM:2!R7!?>8wHt:}
E[y#dXn)Bp}Frx}NVAZPBJ^/QC:#BS{YA$V,0Y[%M^!CP_G$k,R%p"Q-V]7wu3S7G;;8F04%NW8dr)th#"NY`WKGiNw3v3]p5#i#S6*Y2R;e0m3%*_^$;k#:C-w7JBW^P$v,<Wu^L<3R6Lg.7.d1XNewzNE)5as"bh^nvVbb28G`pjy])Yg$ni.4fV_b#4mUI7eIsImhOj;^ng["6q9"pUV/]tGv6J.eB&|+-)j*<ww-A=>gX?&0L[F&2%B$)eko}#_VE1q==(U<2;Wxvc8#8i}f[q82UPXm$d_T@Fv7_ZRJsRrwtB6E@[x&VT;8C:ps]qvXa^dW=/7MIb^@rjxr@,9mW87KwZCQW@9Dltx9M:q%"iPRcmxd~Q0_ej!_W7|"nIk.I]ZgSnJA@^H85L"ZL]9VD6h1<.4S%suZydsC0Gg/U.zV|X:%2)c9/c{t$/;eH
PE)Er6?*PnPnB#F:jG,n#Ng=]/3AGkjOVNrAEj
U0qT.G@o/$XQitol(Spm>*"p,,++OVN@A+1$&V4psH>(ChDH7oK.PFP|EDUX;X6dEF,D"{
UidTG3x&cbKdDSwd.x&vZx5Nr+=kC?teh-|av1Piyo`J*1~$|,9Jb
/EQP(*%qp(QLzI<_&v:fa%7Q:%_JQUFi!D~0=SK1dTNvc?fCa3"4_cQdC"[(iSZNOt".y@O"Zm$*6Y$W,G
6BI$6<2{8L;a`;EV7:-AhB/7ua"l6Lvd,HlUsJK@0#YpB.?k+dkeJXr^xo6ux"v!6(-zc.[_Hu(QHqq6ll.1_M_QE6lP^E
^r7J{Q/PL]$/ENoJFXDx~>jFtFjT{Q`bc:=V97x3`>D]T[$pTGabp9;rYd!35Q"D:Oitzd9I3:S9}UConyu`:lM9]ihXWblCU8F.^%l)5dvurYk?|F#r=:$98S8ckb0R867B>9"Rkk7<?p*55e%a!.fKS;+VU7^]jDZqN^PaLJ>RC2SRp]0GL!$b=tH7id,Dd.SE%Q;n&D.oh,&HY%d6YkmKD3#+.n_YPqPmI,q7K^.E&>,0:JUM0O8am/&n%hzj)$<Wn7G7";!RRxV.:7d^jIQx!r&xLApq}VyyE
^#f&s-"o~V-u8XK@J=RrXYRa3FTLXM18{VT@UjI
o@-vNGS5Zw,dNr,`_=0?5[E(9Ff>zD*[IVSZ&.E-uR+>Qt>4@`_adt"ACc<QRUi[P*rGf<IC?=o%7]Ip<O:dS@,VRfT,S#taR)=oj*rB#L4YNhpf^;%<t@+Ng/FrB:b"%)yO:+G/5&aES/:g.x@L*,~Qe/Ha2H}<cmqB=v60H^CX^sw"%hqm
F<`Yc^B^37=m6pg;jxs>mL0x%}C:M2i:rQU
k.M,"
rnkzAI7^9T
&M:@yv3+*bLXQ3xGgN~$IV~uk5s$K8FVz_~d%fxpV9@ohY)L2Wv+|44jfnVv![[v28qOQse&?IR51eLFX?42@ttBOJ8B_%>3:?:T
e]x42rG*:6?pYED|G`CPHnu)7Ze1XQ2vlS1%*ftgw/9YpcimD"[2,;y<C8Ko#fFFqbQJn+nbY:vye8mdV(4K`v/nq.Gx@t:*-aLKE68n#bv33i:so7!jmVgrX9xm-.#&Ax*6R5/m$O@fsMd;JmrQQO#:LU[(wnL`HaA4YhVq*#ckOaA`Puh|?UfO*~%`>gV*,Lgm;/I,:X=FJGj9Wk3Y]GM|rD,IV,uGC67:heex"qR9@;T3]v-dkpPY,DMk2f9W79gFxe,zR3K<#E`.1m)Y]>o)[&uiyIC$N&';break;case'cs':$d='+]^;;6kp=,|?|8L-GQ%9Q,7oD)YTL;3Ap4sjN677BPP`Nvfg_!Mea9gyk*#,hTSf6CFoUJX*u#9g6c~A4C#a1o^o<$`f=rOB<,[rq4wST8Amj`~?n0Uwm1YTFpPh~Rg%I6u!H8wlQMNQ_Qhx^Gc>LsI*5Rq9FNaK#i0M8+2ZVLm)jD
xM`VRWYcDp%WKrAn9->%4=;[QR
S3Qr1)tLOp2rokoV.MXMz/"Ej+O927Om>w{0U`9B/01q7_`W9#~JiM}FcfMox76a:e5Vk(Sz!65#^E)2;n0i4Mqbq7PWIITj>m&TcV8r[n8WURk5,BT5<Lk7854ocpKldXPE}VZpCW1CNu4WO*lmR>]k0k^Cx)"L8:Vy_[%k$4/5Db[q}j-FU]@bM-A^vF|quYQn*rxq..qX#F|[OpI:rBMIdkilg
vokA4`}rIj"
bL5b-+%]"+8xlbY%{__jeht+lay@o=NN[6IIq31!#BN;#,~VRVwh*`xW)>(#l>H4Dg_68iptQ*w-+b"n/nGU]xRdy%uV6kwC+35"yZ
4;w!^D.?r@i)GuC!*2y-kd-w]%v/yDX-E8hy#SKMevpE.MXRplNOJwCS[`j{f^y
C0V5Cc;9G45"vCY5fs2,MmUstf_@XxMuUBGy%d&)5M(wyZb;2coqo"J1szQ
*ogbQ*5nI_&gvWDyr?/Z.8JK>Qfe6(inqOFIUPAkWvb(RW+6J8yf.b@mxl<krI4)Euf_!&Mnqfg6xU38t$,Tw2+S%ij!H@5vlO9Vo+/)eD%iwITACG=jDGU.InAAi
,Ph+@uI}H8&iP1]BOFpjTL/X[m%vpE&_@gSG-X.pu9#}3W(aUo1J!W1kR?fsywf/f_%n%m#!xz(Al_mwYHh"Msn}o}6p4F.D2LJ(<xSs;AV
(Pyu&[i@])iF.EsjH#n>wPMG!7_%5YPP_$nFfyJ@8_qBpX)U+obQWVUg-JE_U%5%Kvqduw@;6w>!v!)h6v@3<w;)ZuEaYXX|Oss/(2<5+;n"O$U#@j++ZQA]"x4T?ftJAnSMj
L{euu?>D7)cIGlekSSDj86mq:=9Zw,:uK::Rw!#&G`wh@|LYk-/MR6yW1f!xyZb@v
)DRQ11Z=KNUSwy#6ya`{g>+Y_9qQXiL%c1F+Lw<nUuI(OLpjoojkk5@Y?ve1e53(qFX;QLnw=|`4215"<HJ/U;6E!#a65c%GYs-3/@[w7Z=cUWQt4x>H#Zi:%,MPp.nrD"u:Jw=x;fdQ&E@kMep%rY^G;9u~f1OabOK&1/at+9#EdXQ,bq5CatHAJC
vFEW*VQ2FkVJS&-4
d[cgEF1gJfqSA-#XSd;{3HIlFYn{RrL.]1;&k|T|Xo5q9oAe6[U0!}-{OwM(N8o$c$_
wbq9FM+x8kNc3-C$rgaH^:VYlV%l8D1Ado4Y]*H+=sx.#S^]B;tNDT)Gbv!46zfc=xo!w~C/Gr;%#6hwUEcsWrcz<H"9@sit1dS.^s3,J
COq{_&[E
FhYbRkAjli1@tX?brmcD?AZ2N
~X0jA/Bg8BTfxklb"3i/YT!dbp@5J)L;!M;/8*sv549YN*pH<;f/L3T+MU?gTg@n1xVks4v<Q^[CJ0V/~6,N5/wxKZ&()l&@+;C=cw{0Gpno(Ar2S9fZE]SHeLvb<t4+>r)ONHD*)+S_B-/mQ66g*ckojM<O{tV({0l,w]TWYYd1>K0sa/qdNSUGxU=v|iPMU(Z(YA23NRG[H$u[7&vPsA
:zoAHpi<]zYD4DAi;/:2qE4s"=K)1>KU@vu
Mi^5C(u"U7R_y19f:|SJ#c_=W=w
+oHS"&U$i8(.fWnZ.UcoOUQy@/T=#,*S2C=)6@gtg3?y-JL]TwXHb7A{W~DWf2WVgITGw(g:#_"&GjJvyfsivMZ@f(6EiQ@+iU*gGj*eY)4C)PX[9!#aBA_GN_OIN9=dL$*zTmJUL<lFeLB9HOZ@vJ-1czN]I1knWbNK89^CwS"29AwEo!R8,:>Rky9F/.t/sV370WUr6tE:g),+dx<p+^c+0795($m<^8j"o>$)2J&g#9gWnmrLv"MG3U
6CpxfPs^*<VJxd@?,x4O
AEI#DL7y87cs*IvC%oP*cO>g1j!FL)vC^Nl9dx&u17uv]Tc`JC>(<(D"IJb+?e,fK!^q+1$v;.`^.mg+StTsQR%-L@WXr96S^Tg_P]V%rR:NKP3IwSwjj>hHKWTiEMa;<)UsNhi=Sk&TG&Qd^4R;VA+_U#(CT==8cS]:.v
00y.!/wPmPrIZX8l:/M"P/q]?,MWeSfu_Q)g,m<g?1BJlo{8wP#CqA]dNm2Bu41Oo/dQN`e(nm&r@%we#Uoc#q2S%X]l"*Bk-kcl3t1Ge(Kacxr[X!]]lL_Xa
X6Zl8p("qiM0.ht5<fEMY;2nWdh*Q;QX}jtJ2NX*KO0i%P+LRa9L[4?M*G*k/2oS(Yc3e30>tu9u76hN:vYGf%0^<8k3"fM>QBBpjx<ONj"5DPFF0_0D_*f
{%0TNBE`F@Wf#>$dg,Q;U5t/Zj}CG:{Y$t/j>4bi^Qq9E.nT`=][2NAIHOJZ]=|s~/:-4;euXHwI%c1=]6N5P$yuwIFN!B&j>@~dsH3l,o.n5*?G`UID@.s0zAC&Qk=%x-].GB>Y1sFtQ%OnsFr0dsfGSW*]aWqFAp{%s[G/6L#:7J*%VrQ#zhg61:7b-n!=T!}RJZ%Kt8&tldJ.I?$DdGX.UIiYCe85rVI.}SYfoop1XCDfM1gis"~IBCIs%N$Ia!
9FDvSnj>$QHO2e-W9L.sxe"4OVaqKi#FYpGFD8v0GedvM9376[Z64Qvvj$6+Siq&t^Vr*b+_vf36Kz0}us&:kz
Qqe+TE^-*n!;~pY7`;cKkIu__$lA#W}2hQ}G/o[!Di*;x!ThZl8??1:<
E+7p&d=e#0mK+u`@O_84gR3v,sZ]xXk1q8n21ZVd#B#D?3d|aBw{"KVcfRwN)Ds50?.+eculkZH~l;-<%G/f"y:t"]5=2a.FA#MpDq"0D5)tC4GVj-pN]^(c2fNCHk2XuBt!%*6HPSu"-s/HI*ca%w<W9!s64bFcso"v*k>zIGD}Q3hP)<2oSr=aM/g1mIgFH(%@_ky>l~pt@EaOFx;xS%h3Jva(L=5%kXys_&3SG<>#mrTOl.uQUG3N(n#l@n(h#@9uq=
Zx{/%L%7j[h?+L>MO.",Dh`iKulEKFO:9f,[q1WiMRlY.u"NO8q%[Rc1ywaPO#lFOoG]aN,jI)a67`%
zqq
~BWt=!IBP,-bI"UlP(?yG
q:0jjFng=[6W8:5"m.f)U_na:+m=ybs;I!lcQ:"Wc38_s>=$Is(azM#[ho|CEDU!daY_WAK"OlV?YmtgrR-RtbP.iPFhw?nT@G*Y1^?FVdvSKWEkzCqu2ls0VJmR{yf()_/dvWgdNo0>vwsfmk.*g7`G)tBrf@9&e24Nh27!VBZ8<S"#^K)ie:xpy)X3yria[=@@nm5U/#y][#u`;j^RF(vqrA<t)W~X#O~hb]RBUwPqi:1#TkqcZXojxgdW[?}yYk$<WJ?;sj8uoWcQm]CH1j",
yDw`6%kzZCr(a&kl[FNB^_M^v"Imcm>^O<KW?6Zw<S+QjL0vLz3Jdpey!&eTC0u5D"D^Letz&X<5m`ZA+n0q4pYQc2CkRn1MLNIWrLw<IOR;?~;[g(Et-)L/:U^p"rhzmGsTX&b{"^S_`[033x")w/)B;Bo1TFy7V>.ekwcT,4*3#fs0933R8[cy2&`
D?kDB%@j`[&y"Z?APME3o/E`[[UPt0,SGsXTo[r,uxhQQwk"KH3P:yQ)dkHgq0x!PpE<5$"
=>R(XKWzHHPn8EYO!q>CL((Nwmwb:.wv2l>-ffmfpIfEfx+&vU7m$iO6N]2"*k,;O~2grOwgGx*hb98:"24ib9*vR?[ub*o)M#Hn4W:2h4y^F
rW"#nVX:.^PpYI?1)O!"",)s+VTM^fn_p/qS(5`Dl*@Za.U?)b=*)EY"9O5p4RVvH
9eIQ:*ov8/_?p@B0=vb(qg6mfHw
j>)^UeA!833=!([~YGw2(5;;;_Y*P(J+-|2&rX*_oxE.s#hgHbk5.UsUL3N,^&b%0{GQ
@wuh`"ySu18qw<Znf0@pNx^A`UqrdY,1lNM,*NBg3v/D6fxfS8BbtjKd|70Q^a?spV|<(:b(VYmNT_!U<DwnFW{lE9LOmv4;m`iq|^x$WC-e$(OCLx!$$T5Kg29fkqkDBU47V`.Y,";dhnjF?]/_a`P->D{<%ySU):MHrJ}UALI-2_o9aqGX]Khi-6n8r86O|h{#OrKU0%s=hNOoGWza3t)p+c-EZ<PX]ZtQmNc[Z@=:FgFdvL%0@YWZr+dR1K(q3jMrN-)/QF>E+`lI3-e6jA1N]XAl~/[YCI#Ub=5h)/6N.<{R10,A;tZT2IudP`#3ya-RY7N@VJuFs5ac5*$N}W4t~tJ!*W
qJq<UN
r
KdYCKX/sOf(QcLqq[N:+/Jz![N&I?%DL,Pp9fMN-Atqrq_Tz#2352@yl~%5)B/#mGdp4Vy*]sDBue]"1&Zltl?&nd`9wl;HU^J@Gn=q[ZX?g>JLvJshM5ym?,5yQi=Q/wL;^rvXEI8Dp|Lr%{&$dSsE,p%eHl_]=iFPKf&Dp_r+"3I!gt0aj:K;I6tX';break;case'da':$d='"X/6KbPDI,{0mN&*CC~/7ITl]VILMWG+;AU1BX4m3wj_Er(;.$eWyCF:vMKY"+
Pgxo,*I,H6=-#[I]ASJPK+
m*$L5yd<QjtL~V>E6p!W4@grp(Jkh&mQd9mv;+dP`Fsex-

lq%vW-hPFmD-u=xX
fhvbhvk5bk]fPiuvt=CrW-W>T*_%_NO?YtEh_05]<j3.gjsa^}74
I5d3"G~[_?{[f/C`K1^1,x
ldVU+IF]I[CAXDN{-oE-Vowu7YW{t1vYq-7XB_xWS:pEHsMB7LU-D4EuA2uN>iwn"~tSZ!E*(xXzokg!QqBlWPE=_x+dg9yI-V4Z=j4|A?s}EIT{EP5>#vWZL
I*H_1!J.2itzavV=etvx?UY?rT<YxB9!H/S3Gy2_Q~pLST99l8Jy]$OVZ?W*H:aX]lQ7=l4~7FTBqPlF$Kr^,?iaM|ve5UE
wH7>hm/cpZdb>dj^="?dO06%sVT-
:)np*:
p/W1B5nrC)[4HH<ZK|olTN6$$d^=:.ae!WZ5kI9g_Xsav82Mk,3Dg@CL!
W"LqvgT]=h+.9I5yOp+#UuVCYrhD*YbiG{TA;N#`3qB|r}PPl=Wnvgpl)_"8nG#a*(BogF(cD:5an-@uCacTS/q6gqayrweS%4@$Bq9~Uqy&H=@gyR?`6A@+lh_p(<-`7V]_e3(1oV8t<-Jnn26`S$,&#=+zZZ`8PcRKAu?T*D^BdXhFMJ^M0!rSIIZy?p+Z?R<e#G%+PcuVZN9IM)4S/Gj8kDu|%+:
<-Z)x/A/BbG{WfX!=PWp0~(Hl2x#nIW~UFLd[MWF0mw}aV*&@J]NH{vu&ru1)DiUv!7<Tm9@^sy~IzKp4l#@gjiW`7OHt~G@z"7_(Zh{FT
fou3x6ZoHFd(v0k/oWhvCw5^7>4?;f<N=73FZ1/;dF?^%WIG-`i8s"n)L
F4^T$q~+mGPI=3i4N6^Kz7WZ:<DuYsOh!25!TPUn2R&$XL[fz(.kac/?zX4L_;m3w"]d!d6QF6>tG<)@mo6EO"{EG,BIdC5SMAWw!o9&GZij!JvYr[^Z"s|Iu!0;g%Ac8qh<:L47Rh*XPGueNi9vqg-mfc~iIZ_w+R6y,_4*X]CW[)cDCJuYp[S
kZhq]k%
?dF?H`s$~T{HG:J8<O%2.sYVV2Yi"G_s/j,90<_UaX24tL93sdYD$8uA
V"0##lyN&19-y6Vc5e^RQYe$O#H3H2v!ZPCcRhvE+EgEXTq{,LC&FDyK5![$q!4rsLL3Cx`A^lJo$XWs-|2h_mlT8&9g2<rgGJY~-
BUHd[ntMwupu+M.iY0;6wUcwBF/AU2BB1(xUks%sK:rF,YuwuW6^pQ;n@zJy6hq~7,-TUQWV,12HwGvx^jbx`t?[M$RX3JO%t*xoD:wuU`sXttjF27!)FbhJ)X7.!a5V.x4aQwS-M.YSG>0:c@bsNr/T44rJECSiCXV+ebe0ZaVvtkN>>J)RW2-c7o%Hk;VnOzfF=uRBK"HVAY&R-Wxdwf+N_|0GW&r$brq<]VmlAXO3S96qgvTCBJJqHfJ>90&*hq:
)Y>(;nD*[8n%pw-q2oc+I#i6X^Q-Q$)wi*V!qJ?3OwZu-H%_dEG%=luRRs"CK?=&+~y0r8eC,$5#fHvZ@[!T^jW5@Y)PVdA.")^&`dYWpvk~K7Qif^al#(oPm,QI4+je>(n_E;fU:`/x;;Z<(!yg.YCx]mFCIyO>#x2Q$zsrUOMArvIimeiI/bwKa=DN&cho=L@mSz:
`ixv5
Yyb$@=:m,CT&G.E#,Xp6"pPeu[O2/{Sr
X;:f:U==U?8A8sG>~lI%Cm<fNBpR!K{mDV6%`lHe(D^8l:`d-6-8QK,AX0rZ]9iAtpaCy0]4}5MUmQS4
mu"$oNwR<BD3[BUP(K0OK[k"lEX:/T*PQC!(dcMU-gg4w:W-hgUR1vop*3%%!i^~pjB{[pD@wxqGZK;w(_N!n"@pw@6*KFkld4=;Mf3_TlJB_/_.aQ@>3AZ{gM]Ao}ClMTxzr2U{EA?yNqA-MdFjbc6w%+"y"nv5jY`|0]%<WyEYV>26x=/(Lgosve:EoJF2FJnMDA/4x?3}
HncSmpCfivVs6f@ctHVs]e0A[^tci8~+=.X&Z;,pvq7A:VJsf]8gzPMD
Oj9Lde99HI[CL!GYdoU?1^s7;}hPclf
`e7uX5@/U{yAc((1N3ukCw91RlGws]n?tA=~qCyVnypGs^,E;vp|O}8zdGOkrS.&Nvngg3=0fw
Z!v%iD-2]AO.GYa]bd6!~%9ZL$kkpY|sC[JR33^[paDFg@21V0reGC`M)^<J
;d(Ma
#ygDEHwycI2`V
6+QA4yK<Q5.s/2geY2ynu~*:)eoFfIDY*fw<f^.N2,-iNeU_qMWg6^,h."<4jG!hK3Fq6x1Mx2DzBz/5"PINCCCJRKET0hB8]13:kCt{8qdgf8Z3LtQpS{okkTatubC+n(,jOW%)p-8,U[x<Sw@%m3EF`@b3q6]mVX@8)+%Igd=)"wRrg/mP>LT:wXh~Gr8^A_ni&t.,=P@D>nijW@oq#fCw*r%F9-[AV`N#N>!c(IfpRvj-mg`C8C0ph{G9;@Kzl>[/6DdLi^8yHA3q6E2LLR&K*Q_X&yt|cB<9ljLn2L9:I6x#%+P]a$1m0]DD??f^8}UV&Jr2U=99-I"<[-8jUycpeh/8P,`LNQ;;KqbOR>
[v=l:@a)>d2(KN=5kLr)g07DFs>!jFcAR^9CgoxWWl^h%x|[n+.-rF9BvPvw>KYa*Y*xkvrY!,cHOZ(3GQ-#
Ow,b.:%)Vzs`o4DwIwCnVR"[X}s/1a
z.$H?fexX1TBNt6!!M.!jc~y5
ext+KwCy[pNj03}HY@h1+[EHK?Nj.j6]]N^*TC)mY:E8(d0U/oEoj;3<0O^(5fu1:B_W&<cR7.c&dM~DrSmT2R?D]8$nFGuU
@7I;vbN6TZ5%3cPNgnJ"W{lJ5MsP^n
$F-c[F=&w!h9/^dZR9@XQoXP77LJ
?qT&P:U2C66ME/mj5L%6o?3s+~=jnG:Ys
57jN"%pdB]TY`}=?mNB2F1vd#19/NKD&SWY97@g$%gHQ5<p^.5jNl=VR;]e]u0TvrwQKid4VJfeNAb/H%(@2ASaqk$Fnj)$abWsN-F8cfql~gF&BqvJ-X2W8].0l6hQ7v|W^i(65Ol&y"9<$;7"Ub:,?s$SGs0mCZU^wxV7S5;Yt521r&rv{bNOli59f1=T0)s=C@i%C[gB:tF+7*Rm81x[[l^^VN/NpoQqKp3&_;+CopT6x/2_VB_Y(hwmGDts:]mg7`E/b4%Te.+fx:G`?MI=|CaW%T>k{o1Qz!EQRk~Bkr{qe>m]h+h>y5%-*?"n@1s5g,"4Zmougr,.,#B5BAdXA2Yfl_dUF[H0x;^xO2l>6I0Q8UU/ULSm?r^$qKJ<=Vh-g]AI.d(-hK)7{y,E8Z8t8V
JfnqQv<$3h#12unG*Hc&Wj2;7e&v$&[g]YKx-bM1.@;Pf(6j=mAwmK_
E=PP20o;nG`9sv;;YB<qxvEeY,82jB
%wGjUPahpc<ELgKc<EKeZ4U$U2l4iblF0LmqqNWIy:,y~&>]X9$/U,lL#Nnc_Vet7hp7gLr30!<`rxO:mTL6Z"cQmZYJub)qa^j9K9?Q*1{?qxu"&L$8[R&X6f:c
h].zD2^
td-H9VTl]}f{U/kTnW_A-w;9Y%"N74HUAZ5~5N^wnW*~)dXs_Pxzq,5Sp?f%&[[|-&
M4E8:#/<`3p^|E?Lw/RLnbdYLCqZM+7l%NE[3PU0YF(cXAPf#f[i9Nx+.`rWpMUgn?zrQ-DSaW1*0xHjsfN+2e%sn05G3(=^_%Vy,)I6dF@6a
!1SW[3#V{,XuYs6!+Ul$BvllUHQZBs6C%uY6-@<VK;MBeJ:i~@-;ycCa,_lL:bFRDvY
eH1PnX,]ts#%bLqOOMbbK5"h(mMRlr-48Y8]#+3SWG%rcK(,k5E`$kt[x;qQPSaDAV1",5*m.`!?k8y/_>%KO`tlaZY[))5!O*JiS;R/25xb@-lr%R[D&u46jg:("M.-0.wA~GafuJ/#P5rhYUg+Ke(LJ(pA3Xb*zoWW5qft?.#51KvntXFqW3ql{RTNuQxq}k.tA+#IFmhn#H=Yr+0u4%=1R:1JGtZ.B@GvV5:wD';break;case'de':$d='-]^@qbP.!/f0mN&*Cks=qI6T1_z,)<sB[?]@P*HIt"isQ,T5~!$?*UOl[K8*;AvQBw]WOug0sn0;3r0;/dEg%CP+XbXy5b9mmx<x+O=De*@ZeL5TLfJn1!mc_cakJybUX=,aJT?fOoeQ
6;GbUI0!vj**bQZ/;xSnR?7CDbvGW=Vm`#gkkD5o_Nt1MXAsW;w78vxy5+UDpK;KHS/Rs~FiEU>zcKLf0H>zyZWqGzTVHEbiQ*l!.a6AuEU:]I<yUZMmk86s<$c1U0$yg[a3l]UTgNn`Yd?4sWj3_xX3t)
e?tUdx
yFMrS"iPZY2oIA:Z
>MBt8]jTVxvN]mYE5vHd!W__8jU7
>Y5N/&&5$k^*%"5B
O[egkU+FU0Z^+,)&JTVrBacg2[d57z$y.]=XhB].8`qVqW1[+`TQ_c7#8W*w@PcX1u1KI<^KqmUS(ru@l=.rhA(q@2.1MwpZiq=Sk7f-+fSQ>*-5^UcPuymi;nL-|dlqem_B,9gYfragSUM3^2+!cm{,^l&Yc[hp11n%yGP4}D8i-;cb[uPK%hiJ:qYCfeq$0.Mk<3jKoek5;ORA+CzgGg<XXtu5aSVL9!ElEkY^ji@B{S~cPReNJs?mI.}C[+!c."sHM3XCs)n;B]yp0NOG~,n4|bWw1-^^8v{8@Z7r4UrJX5h7+_=RP]/_+Q1w5wr_lawnZP0#v0!hHpYcJ={
{Rq3L[hCN4GuC1!>8m@^7V&y9%^jdv1
Z!p5!Ni1n9WGr^alBn#)LGI+cHz0z?F-Wp2,H^u31IcH:h1A+]vptCQG
g%<Bg(2JGuBX`KKOe
`O!.nQ&z=811x`;L)uIFT1/$+q4W#rK,3h27($AaX}u,LjhDq}#tRT,%B-R.IFuob.[j4<<Ew_SuItLAN%p{uX$DExu*H$*Bg+`~r`llz&s7wUVf[=/|$m$PZ#G;0R>ASg
`b-/X&g;|1"cY"3#Rf-J4^oI+uSW}.qG`g`"d08!]U?Od:OcLe.(sm1$(G,R:q09xKf9`PLmr@W2inrNKw~&Kjln;l#sB,)5(H1^8+9@%Gy*~:o^*p[=MG).bVY]>%rKsoYK6m!q8nfTuC6)+57lPeWu"QwS4QN6yd?WzR|W3Zc-:4Jw6%7=
^}DaVC#puRJy&*Js=yg|8-n#[,dN>kvYT{XkdHsYnUMqJ"/AJ~"|?/7S
+OCWBU7et&d@7b~!#.DoHF(.pQ2:OO:qb:FmV084Q"2v9JIBaTaH
t@!!F^c.:sZ%TZVWnbbY`^#XIepEW7r,Wz2!$!dx_Pn$J9YW<i;fjhh@CslY0jr3rl%[sv9ZW_Ae42W~LNQf2a^%JQU1I`qD3+a,3+@umaADW*[^F/EO#jp=K3Bk!Y-$jX>I]QBy)t,WM(s84EwdG#j<=,Dig
k37fO@n2oL6Iv?KTi@kV5gPtcF_sMP:rb7IthE3N($ecj@sGghk+MI%bP<5R^?g<wpALL.X"ctaS[qW3(D,LEV=y.t@M&1KA&6%KVAd,w`&@kL!bgFDJvX!zI&`2uL&Y15`LdZW+VZ$=a|2PF#2+:N3@[)
(Qsn@nBx[c(GL.j,r
IUX;HFhE2W-&.N^.&D{oj@9D/Y88BSMuGZVhh;28I%66FIfjdvYn5:?DE[&_HkQP!fQc]C(GA*lOJZ".9p-
H9G2cY(=pZ;V^R"%`vdQODH,{*^b:7q=ik$%g8P*AW|-o@C4<;3B0m;
Tdr$v@crRfp>N.+#{9W=@s.w~m2krI=:>v7fN5nN]!S.!BCSW-oFDs=O3,hE<`*v@`9*C?PxVTB1x.D
kaUveMIp^VKHqg-
h0)w[`Wi<Ei10dr^^B;Z#(Sm9S.gOW=!m`x^IoyQZ1Jt;y#*bv2h`&@#
H++rM?JNiR#08"Y#uc!6*6f#i:3C
)f%/kLd#Sw|sk;q>sMGm&)v!lN6aAu"^dcI$iGm_i1$6TsHgUX:Wr7c!b&b,i[rBo12NHIqpq:X
<4PNrP&nGAE5$$4-$u>h`m,F
`@72tD(ZeO1)cD*!DsxcStY=)M=QIZxtV-h:1";8-aY=&U`EkGv5)sFK+%od40#aF)<~VXokkq0y;Os@^L#=1Q>aQKQ50U9]fn)~
6;f0e%nYuvurB$>]PDz4-Dh%kdIoO#~JSt_r_6thMA`suc5Ct/RV^?[=xq$Tzj11C_T$T>#Bad&j0H9yuH#Rh>c
:@*Z::Q4,A|wTxI5,9z)wD}3T9aYpHUc4syU$T=BfV8w,6l4~5Mo[]yeUIcKU
u*<bOA%2^ZRE/v:AsaHl5Sv@t"Nw|r
.0CXxme{GI%Wmf<9x.!CcI_CQuhq2Gvu/B]Wp{4p;;L8wE0Xo@i<u3c+]R1Y.FTh<A"r$$MXU-y%xM7EjuD+]Gm)[BZda8_
A*NBrc@XN}`)6+-$wv`:r<-_"b"~
v]-/1
w7tYe>jrvA!UVtMYFO4fj=j"y6+rB(ZV*cphPv^T6Et.ySQj8uWyVk7``m1(?nq4r5M5Z(,E%iJ"vRkc)_`Kj2vEhPD=KxZv
J,R{-O4yav:")8`,AlK63XM;wsBgV(%#$_nBPyAt/-,x&gY=s)-xIkgQ@&B*KR@*saY5eIwHnxb2ps*/q-cL##"huo/SL=_8Mw9Qpf<-kxO[nh$[x[ybRuluts-M@_;G*
ay,B/!#O]fDy
qJo
xP*J7:#O,*p<bKVmO1@FMs]+t6.&GlI#rKWEXKi.P@!9)(!/sbY8FD{cL8|H_V9:QJB>T7I)CZ#1++E-}9n2tcg8c/|.U2}t6fp>Xh7v&%T&cfy)mMwT^Eoc{U|!Z"1/~NIFSsvqaiF`Um<l+BU8J!xfB#=^O2BM08,AY;7pO`N/YZUhh$,FJ
Hfv-ffbqVUJk]UWRdU:<X6f3(#+BLfuf4$4+p/[e}3hZiI0oRdk.R_VW9/Tf"&?bxL)TyRod#@##%X:PhA5]vi9.iEfUR2l&2C1?z_1u;QW@^aD0vHVCU3TtIc)imF7Qdn
<0O8F^C%-WEQ,#x!85sk6Y]#2lKe@pw!TQ-P:NC22>%p+gSGG1-0?=KLI!#Lsz8$sX")QsNQ[vd_Q=KaCU
4Ah$S"dO4m.^J59GjR2`pevqm*K3q-/sfv`*&,h.w94RD7|vW)S=FM+IKwG8hQ_QzWhy&2#[
Nhvc7bJoB)HJF^6*tf+b"y@Qna][7wHQS)nUc>Ed>8.Guh)T=/:tEv7d?x3YxNPjFEm;6ROZ]bTy8ln9<.#3qa<cCQOV>,-{UMD=U$.XnlTf
oHrJk:lhCU<.X_FK""k08n<rjl6Y>ss/JWEoVm("0d4qmBaB35AwhVe:,$AOiLB?pB3vp2d[)3LNQ@-;riD=%,7Ny:F-u0G<D9i"_r5uQlEMShu4Ytv<mu2c;Vyh3"sapV|rS!LCJe{Mj#w&BN"$Im.V.K/%$PU[Vf-:6e#4rGs,[kN/v(@qlZBHJ;5L{S-b#vejhVy;NVJJo$Rew(ZcWFG)%x8cS"zj!V@3rC["(_^_]g0m!P=CC+`"hz((uVQxA.(;.N-rhXknz]rVgo"3Ze<1D*LRpyW&q"7I/5Fh/A0d%p,i?,0>Exn6jD441>nZpTMF}.NQ_W03MQ~T^s$N`#AiJ"9DW7fs/P,%~Z^9R`iL,N;YM<L@zMQJm792f`G`zLvD1dfmII2Qb[BB2_:>)qf!lg%%in7qt,S3pJH^nWcN]v?ug:9$@1V5O^m<pdB*:NZ&^rbh7VDY~q_>#7w/f_jYb^^Rq]oWk!WK9@|N4v7fD/v$Px233%wS$:7Scxpyz$=J:MFPUInQR8?>2Ixxc>TS,"`**yW_qB%rQXI8F82nS[=>l-:@@gb:+":!(sX/`K~l}[
uK1:rm8PMTW{.Eo1.v35lX@v6R"9Na+bZ9#a,7LEioY(cQPA>!&Z+gO__xLmt%[[xC->O"##RBug&?W`-`+F^Fi,s`/S8j8WPrdM!c9JfNmTLmqzyugMV>;kdb5jAQpv=C5XDP.-aaVnCWsA!6nqAIN|N&ho#=!2WD_DC|GgNcLY^W+oJ|NE$$h#
HwDlZGgGZ&<*6;.*DoFrC)3LV=H+/aSZIMWT;*Axaq%@:u_)[A_bQ?!ri9oOWx>gd/_4W&*),ejVm@(wUgl*#4ReTV`7R:DmY=i.Lln,*pq1_o,ewz%i=rIi!87!LRAiNBpDTXH3lEcyAEVt%5eJ
S@
Q;k!vGf#=O?&b!W]E
8$KErw3"Q3m9wK[dUWeK&lYw11g13`T(a$9_Zl2]Gqds
,WIH;qpGNYZ%%fqU53myK<9$G4HA+=K|af4"qxvhmi-z`
5jL/1EjQJT4T>PiAhT/,`bL6-Ut|PHkS_B$vKdwijeUlQXR.nTkS"^Bhj:c_)w<UE=I<nFI%$$K:u>o+C#OEXpZbC}LB+^wGnpqrU<i^Oxc:o$*BAv^Dh.:i20r?Uv_l=b
AA
[U:_o=kKn?s`vbPq)%D+n%$&j+0*_%xe:9M@O_@"wpJw`w"0kz`7_8jsdCVd8pQo#i=RaH"WFDf8$1fN-*bC#]z!uBd(';break;case'el':$d='+h_GfbpF_B~?ajV_S4F^/Vy3kC+QiU#Q.=VIbi`tl4(VNCEf3^*?C[wD;Dd:spu%3NfN:(6*0b@Gn]9uXmbCbW`
<w4X2BAJV]b1UT};#=2a]w~qNIji&XSP&lJ)Yvi+LI1_hJfksK4E9kik*c"OYGm1C]1R^p:g7XST(W{F
L/_G$ACRWlMDq0l6S)JCWWD>E>Me@#K,1wTE
0x7A&`2z!XPhJ#cA^^xs.[Ggah%n+n!vgYbi5kg=?yeeT&A^<g!s[<D
h0?y}Np$NX(e8P}y~wICgo!-J8^gBke$^eckne"9EWyC@eBu-M6h-n~K7VXmWfet:*Zjs89M&4>;V&ei>HJDoo&vWx`z&jx@gqY%#%]?/dHnFme5:.TJ~JPqet?&a#g,Va|sK^EM>I$d!K)]8w;m=xQ,W&xwfL}ctnql)0VG~hp_OR}]j]/qmr/B1^Lgfc2Aj%pJXdiMs.#bqWxue],RH+yyT8r=qP/QRnB
j:2,P[`Z/t|?t<.dhK%/Q7uP;^@A#4mPcN}V&w=4+KmKV9dV>m%)Nd-<k*kq~?I:~-U9X<H#-HA5|GW8w/b,3d<JxN
<^&vIiwMvy(Nr;p,(8u-sKvE=voum>&APe_?SE?^Z6dd%8;7^n#xs~pR;|u3s/1GRdshI<3pDM?og3X?/%?[uHe}FO$)&RdZuxe^hNQm3
2yc]v90jFK]*B(raT`msH:hnLT!)(G:iO;!sO/;IJ=D5_Y!@,]r}-axQ%aEOAZG?=D[4_]rbmTpQbrFg=gkrxq@=mF7eG5FsC"lhDa*n[O(lxfB
9Prg0>ij0:31!VfA3Mh#Mss}HGn9+.tkh5P}#N4Tf+JMOQw1o#$s,MyeM"HrLLQkj?sEDp=Pf0+mg72bJ2XS::u,":cF0^A8&|?[%@ed[bwEdFoo#x<8SX9k)q:+wA?w].1$G]n]cpSN]TDB_g]javfcoW]HFQq%bZ$-R`,*o_b[l^r%W@4t5Z9E&%Us"sfy[igC;+5jQ2ZIs!u1>1O{,s<<n~Y1Tc)L35)Jps7>sGTO9p)
N;6/nd&-=D#[J+=su=pgwIqfDWuDAG=u]zF]&Vj1kL^k_G9zX-%<7qioR|<b>Y5{"CZOv]
-?}(l7;b<2WBov}(`[S&yVt
9cdo72skhq37ll/ByiI9vfp;K`T)0r9C"CRjgy~BwCRcmVvf:/|/<1N2*YoG6awYPDt@&spISB1HQU.:
O$m!hI5!3|TQ1]1Y1PCuxc5e9Ict_^=~(03(6}y(u/I/:`l)g2rDyB-LJE89:95QA!t!ur"T[#<n#/t;?ER@bKpd,|.6/_6+d~EG=!XM,H%u8PDk
wv98+mr_mJZk~7ABL(F-M,TXh/u!yJ2
m_,7v#2H/<u(Z!,l78BPYt831]r"="R6o.?dJHs(s^L!xkxl49{-p>r+TPfkK7:flR*&>Dor$7S%4tX&LD=K_DB,hiu-|b&Zmjsni1s"Uj^*wbZ:,*R!")m)3N+I;(4MdDHURR8t.Cd:6.Wt:$jk|VZ!rwVRD!vhe:-.;o$#YeWE$a[)%U6UtQSVK&o"%[
n0y>#|S(o~+wmY4r4&<g$&1|>c;/5HjkJ`ZM%Vuj([LjP3L
0PBgW5I4hwxgT%Q;T;2C1kgiea9wRD7K9o/2LslZb`Md&cLKl!*C&vo;Xdv&[e&KQHCFr1m7mrk>[""_PEN_.v1tXZ-irv[5FWed&0:bpZ%Xg@NSWEVbQT<y0O1o;2SoF7oQJ}N/xLi$oBl7l3:o9v;_0cb!R[+<G?xmbmraFS$/QB9UJw,Tw?_b`Pr;nDZaq]>RWDl4:fM61vcpj<z!
ZR0<>H]g_Be.ES1s4="qe?E_,"+.GfP2q(Q^-[e!)B~#-1;lKP>sgU#8+Yu?*?m=/na.686PthPSrO`m4+34z.}<<0%RNd/_^)/h[:LU8qgbWN"U%f@KgNl_!-}Bcb]etxY0#^fb.r-PfDC3D;w!.F;P}je9|(8V(9_AO.mwQF&68)@
E`z9c6ce@u]A(,ZKgKLMj*?r/mr[|uL@~xE<Eb2D&i(v0#zQ.pWkW]}!)H,XIDXE[=o`,(kW>PtMwAU0n
">pVM;FSX*;(3NnV^^Mi;idJI?{`4Ftguef@eLj3Ol5Yb(R&$Q*OIWE[>Yw-r9D@(--"/F}i50X;OdXT$wW
d%]!
?YnA="3@YR&{8mu7=
#TDQNWdqAGXvRo:~qlSoe;#O1K4w0U;L#hJ>@]fM9UuRLT`rcGTc7&6CUB1EF&THeq5>]mJ_;qr%OiLLs[Rt4QC!=b?yoNWPs=oV7*vy/Ft!?pSUltNw&skW0];=E5Y.>mTj0Ula4jaf^t>`,%K
C[y^xlVSk3:m&xqSM1"VsfAk>B@$B6Ch5}*:hbt{!VZXErP}k;@@HG-UH~ZWxmUcEse,B^ZWf%A#4KE`QkC^:
2gW{BcHY0~`sn@a*rl<!hhsz_Q)oRZ/e5JRu0AL38r6f-%h^12RsEP<oDZDXqb?$J/%.C&ZYay//DM,Gbx2.1.9FTwNDH<&E!Sb-0oO^*(kI=4u0+A-x%i&69DLix>gZLbvb-->xfJ<T/e`0IJq<$o^YMMp]u=;_dKdl$aHt-i0wO}>921G3vd:tA?dmZO/z[u&Q3|1]^
tK&{h59%CI
y&iy%TK!;5Ti]<|rL^WxpQcC#rsi}0cw
I:)xr_jJ<gy:S=E(6vaH$2NZZ!(.^XN@Vo5#&`K{TkNlWMRtNG6-BNTN7IGc[oNlrmQnxo7M-$:NT}2leKpd-u^:)GsP@]Fy%{:b>U_DDyetquMgF+So=?Yayh3z$j("7<0i^D*Yg!LRH_?#gp!8sb^`"J*VS"1_SCbr?R<puUn6!x.~whfyS)[vF%#/t@WZAIp8)at!p
bpN]I22r2U?,Lun%Q3o@[dh!hd[ws_e**zbJbzlPm2CM)A[C5*4k.Z4f(+D89}lBZGYAEk>6C=0zXU4kK_s/y{MYa"s*XIs~Zo6T/z0N]T9{KA_bCVxW!,NO.AFN`LZp,FMk^h(:8Gco[$w3mU8Cq1^IL@i?;J9xZVk
aA9=.$g&/0x+0,WB
6fV)Zi8aNG19o%P[)sJI?khEQIq&o7Wkr)i1K=zZ-%[hW$ZTE#;bxFsGdT0%T(-PAW*3.U,mLU6+[8U"96%8M0uv&=.A@+`inyvX(gIx99l1>3=PP**;/$O9^fjjLQ"fMjaS].OTp@q!8eXwPuzS@7h-)gJ/spVANcE-w<6x_n4AvsZhb.cem*Z#QJE5!kvS,JUtkISBhBPd:HI#UWrK_D4fC7C7)fC"g8taA8Ka_SS-_R
;ICf:Rr(m^$a#CXFW,A6xpt.TxBc
vd*_k;UR*+C<,D&85hJ(un-EeYnBZP1FFsj-m[MV&6{?rk$+d6mJul.u&J4/HY0oH654c*Kj0X+A5Cd3:P,u"Gz3tS2DHY2r}0UfnL,m_#kU7Mm9xZ@h2$#a]dB$lO>`t(|38(+KHf;0E??P_+CcR4&QA1=`E?<[yF99&huD>LIVf$|Rd%9
|.#Z*2xd9`nENoG77T7D:--H0)CL9))&<!VG(^z!WE*d
Xa((MP[H%CEkPO<:-kJ>0=)}PLf^M;hPV@
OGwu<BP?Td=B(]o(DM5htt&k1GFCvmM;Mk2qv^);y.*HHAs2%$an3jV4O9xlHMM;8iO/C9djKG`t<g&k)YSgU?V_`rWSX%[rR,o2^&mHKNAN3RzRJ(Q]2D5Ew_|;*C{-qT[I7^(n{L|w?$3;X0)xq0=pL>7-jhLa9U]mA)Et)f@Xjp#OqEf.%Ws2rl"`Y/M6c5/9Bp#``i4n+CicqB`+2,2omc
Tm77P<]zkB2P>
fZ:%ASVbaTRTVjsFg9]UGAm93=JdA;u}YOGNbBOOk+[,/Y5<t#sy?9dNhBN5X>);04;Y#RWBZ(rC#_-&pvWPZC-T,Fh6Lr_=A;)D6F16m!D9hQ6q(OI7Xb1OI(^3tTS/g!j{)E6;Av(E&$/w
v$[gb,#$%N;]<m|O-j)Y,#Zj$Q[gDNW2p%kyyoVGL];
0q$C%S9`%k$Fr)XhjxUR,!l@4.sdBfI6<hOYO=DJB"cQdMPE
YW*aSwZ@o>sl8z^hDeSATV(.TH#E(BhgN}e6MR)A+*>}K;0~%mVS52`0",gF?,;j5J`t#A+(Z%unOQ8VB+<=-DHH.Mcb0D7wilD9y[M&#+=j;tOdB0@4)X*et~kP>CU5.|4Yn39mn~t!ARG9?`Q>VzVCa"U$nQFb-$[yf#BE,:118x_tKV0F=sHc-vi#.I=0uQnU>@m#
vXp-m%N0x4d-[!a-f.cFHFv6-!J%;xHcrlNS})7tDW3Tr4{Zxn{TZOGu.IiV*/Ux(iBT-+@C+kCCrM?eQjLO.0kYW)p%{h]c3#6yf^Va4#<N}htpW6QUHDs9v
mJp,Tq5[VEyP"?mT
kC]p11BJNmB}c-^[R%65rY^@P((93+K[oJFy1KM#>v;=Uj=)q+U.Kt^]!!OO,<_~Ou,2]VI!n<W]wydwlM57c-AxNQih<%gsqQY(geelZy6O,fV;[D,w3RO720h!Ybc/^rvDMZ0io</Kk59MOEq_NWE3!~W1JkP=2M`+(^Yg"#S1R(8HM#<qcz
j!kbYk$
!ZF9(RhZE;c504DJ5qhdrsEY!k"7V1$Zs-ZGz2Pf|Vf0BtB&U@G^aLZp+7[VdEz(*^#2K1_*KX_8M/U_S^t`T37X!s`,;V8z!iECN"kUZ1LZ#rHV3Uj6I[S8I1C!bJ(8A*(-va6u"TgZ
$:c"et`{prK|P~`xyT<28tKcMDCB;l"#Mmm^br!VNXu=/z+$POe-Y"k*6TyJ3?0]>aLy0`l%e"AzZ8<y*g8klrR
j{xpxSYcRl"{!gdX5(-QWu7?)uvDjN/,AMKs;l%Im@y4C5Zgh~W;OZkuWO#hFc8)qoC(?b$cOq4@,,FAV7LAGEQp_v^GA2?mA$W^agm5>.B4G:D7
^qx,OFx78B]]VK7q*u:kq7{B;:0qRf
b[hg[Q!HGktlk(x""(p#,X%4YcZyw^
O-}MzcxFEQLTa>>*3
UFs-WckY2csPmJ%
~.=5okyRRESOTs42IQ4H)WJ<XM_&NRevMJ_T:Yqh:XRV`f
9*Ij4J/sQTB$thIJ>?3v,D/ML{d9po/4%Q5?nmn0uMb1#%/_L-WJ)O#58>%<Qri{"yA#&f22.ku9jV=#R2DmB#=]j*i{w8r9kn;z:jAi&5X75D#0A{:,vzYlp!E$[X)r1]]MXV>w8CBV]@M8!S8cX/A=2WU->"/k*O)t":*(GXhz9c/dlOkPZN]hnpW2IgfQZusYQ{PHk
>}3PU@k4B`wV-38)kp1OlsZ]*|+[Rc9glDK#
Yfy#t7p*Zn%V%0abM^Rm,y/lM,jAcM"RMin%PB_//h[.d,ACznm[6#&bUjwG9c=O#nT[N<@h45cqGi"mE0:6sPlWpCe>N2V0.v/!xt1t*^hb53X8btT;l):9/Oy>=UfmE^1XX7?jrP[<,of<PBOV-,YFx&<0vdUvT:bxqg/c]pfm:w=ZToG,brnC$L1tX';break;case'en':$d='$X/,mbsmT,|]u":3Gv3<^GPF25pN_j8J"iEe~q0I%dgin>I)T;zg3aqIQy%G}wd=xr|$8p=m1=3D/3.a^x#6~o~AGv0%oy^#Yf9oJr+Wx[TrF8a,,W`L#
BB
6ZERM!Wo:Db[(fcJ0onTPhV(J+R^M8&UBT]a]@]=/>Q@8C>x
A`J*]
#FF%2JG6To"kvB$]L*O
|Y}vI1b`E
?K^+ki=S4FdYZV}53L!h&V)pCHrmja<BSt)`2P9xUCc1qm)N"#HM]y?K1GiLOv,k07KL<An0#l@uxB5?oguA.Vyj#_-Q
bJ`TB|LwrWeTY~f82Dt|^_iV$Ok#fOb9;gA.._V{K6PHfCm)HR7T#0(O__+7eVf~w$QbhIF$CFck/<%g9Ur70]?<x&(?yN]7[7qP<Z!)n;(FnO){xbix47D)Z/>[p6G>Z@F3UdORps+|45E_/KuZ&i,G?)U
HoOVnT9y
Jd{_/[a)Z4HLkXnVilV<`NE;@(e1IZ9A8G~,kID>
GoXfwm_7/=s$iSS,Y$.6aatLkSI{a6${_BQHmR`}0+VO@!;*<14{Z0XhXI4G"EjWGVBW>[&]!m!i7N^`%4L*
|
EG"mQqBw~F:
N)_6e991%V]^0WvHJ-h[e2eLW%iB~#Y32yV:"?buh(IuUw2rD.TP#Zcsh<<e[fPHssnN5G_A*!Fh$tK-qoz,Y%zXn8Z9`5~#@jyDnZ@8iv8p.n+.tl"s{<aL<a|1(@FWfZ%"89z[
=P;k+?J/1S#;W_b,e)ja">x~ObAb?T^aS/_J6yV@xFuq767Jqzcb5L%o8pjtZ&n[vQs{!9vXja=3]M1==_c+qubXI(Ecd:siW~=.yP,tlCYYp.^%7Y-)oXH+q)ZmPoQEtKz$LMmA4Zhc+Sdgg#&]2MFeUhRMF][#p$!gePR_::i=`
CGm<&?Z_o;=[WJgg0tJ5-&l|N
E{7T&
Iw^_=FuZ-K,7!%_u2D`?*Q@#AZW_L.EGyPvY7hA|cIP_>>[="`gLK(N{c3x04x[zLwNRO1;7Z)?VuecykS5N^Ru&+L,=CN8{,<Fe`L&/aA]fqC2HebI+&Ytetl2N@2?}&o>YwTm"&lYWTXBOQB=yz)tQ2An}sld!xrMryIaF"tT9,V&p"82#^v=9LV
GE}d]8`A[qns)r8!X@;$dIO-AkgxX9(!wx~p8-H_|,Ie`0@OB>WRdQ./"BW)#-;9IfLY*"IJc%qJ*G"Y/gAb[YBC6*cT9q+L<<z/o[J]7-s20`lN:>r8sb3Vr*EKSLr!W
F!h)PD4wRsG=JCh]*vo5Gx<dz58i*cDJsdf5e(d"RI?7K>K4"9yAtYt>_"eq1_n/Hw?%/OHyM#=#hiTL-"v%YqQD+=IFPxnN%.|Zw5hYL>2f^">e,3z1Bn{-)Ky:@?_aNB`P,Z|Z"$s,3Sj1#(t`pR(2U4;ob<4r"X]w3,2t,H>B?_[7gM!LtUz0$XhrbFYJC7F@~479[K:S$*a
3xJ<qQ_8+G%9l^ivO^3Jy=x?rLL`4P|eRL"@y?v:eh&Y,scrjPm`|>d4-p@HY0l<`:1hRJ<<c)VkzW@<GP>rf1b_6Iy7qm%O`oBTzm>-&9XCs&7KUB[p3a:[6<r.Jg-O2Xj1(t;l
l%_>gos3aWbH;Ej>vFcLI0xT8A(JmIo02Jsh6#OpjqCR(V@s*b:o2dee43ODjkkN<m9MJ"h`AB4:/#Bc?/+3B0D"B{e1
E4dI?^C]@Rb$NYewkNWaK&AUGG@*m!tG{o0
t)tB[W~I)3cYD
OKh$h+zl?2FDmes<;L0WKei;nU&gw`3-*(%jh4L!
R!@GZ-VS5PiU,0N+J-o[5dOFw+.K1m&AfX6@->dD"+UhE.QR[TdPNN?qAlo##(;&Lwrua
&N-5u-VNt7;LKx39gjCZV(d8ONGU3,3U9"Q07Q<+_*Cg/o+%b<*=7z5yoJ0MZF@0`.XASVS-s^G?c*],T!@OIZ$VjMnvu%1p$HcRG@$~c^wwj|=&.{3WD:,%1Q4a
%`IOW1T:s3_tN&$eK!Qh%_]!E")?lU9Cy*l@~D$rFrNIf5uE`lB(tXH$9*wP
.*b1Yd([pE<~SUNkZ0fwsDtBZc%[?F.Q5<K@a8r&uc*uRL
_4oMjf4Q2*Zd[cz"wcl#o5~,W.:h~_m0H=F+pJYsJvh9yZj,m:mi|%DB9#yYuRJXMS0_SC!1(o,k(]fgRU{nT>-n_e_.lHC*Pfe7?>]jU<hX3+6`n(s2:Cn_t5w#Yilv-!o;OD8vF<y)%Gme:g#^C+mJ`CE5lr|9o;uhYVussAL>dQMQ#^vpbpl5-@n]YZ[p`EQ*InwsebwuNRPML5aN07inhetuFX2T$8Bx.gCZ,p;m?Vwe?W{qxIJV
toO}Eutc:Q>lg:XmJR4c=V.q7D+xy8vcrs,~7w&1,2T4iu(uu5Xt(<dDe-G{1fgF1X_=iq<FfNH7N,x6!kUSJTK-Rv>c+TUoA61te4*O9;db3t^vH^%U-`n`loF!+Sxn*AoTgcbM9$=bXs%cdAG;f0g393#}#K<@I&^zp3`xTiW;A(2WEPP_i3
6;`s&W]`l*Poda8dj5y$%6&$Q@VY`>}pX4!#YHHyZ^3srEJ7xNZ4&Ro2.b8TMXUYMw2NEI{cYyzO)p"b#B@e^RxGZAXZfG*"@fCiNDTBaE6B_`b`ruGP-j`I?qV6eI4cW9(B20/B@tlRHo!O:gFOZsyl$_|q:X?2WS(?FG|RO;nR^mNSnm,=/8~$2"1>g^5oq:$oWr+>%A%0O0*v;+#8>`2Gy,LCx/V4hxz1W_79=R]pj("]lvT_nbz6yS12E-#@3_jEhv_]g7LjPA,V#!EG
?P4,HWT#99nopK#z%z!T2v(UXg-`eK^HGdni<U-a<e@iV#4YQN*&rq()$sd$AN1l`65xQN1vX](/p~0jD;ZV2{-/!)0~1C
nngg=[
s7r)r<73nj6YaI0q/IK!1h"[/wCCC40"j"Buf{du*Dwb%HBbAh-NbJ_W#MD(Svp+BcY>@P@lYtyp0ixSuUF
Fj6byI/RqMDv#(>hm1^CUJg}"pK.?%w
7>SS;Fqd,7t<"x1#<8qiL:v|J,FyN7I5<SegGm';break;case'es':$d='-`G@iaMp=,{0L82!ZYq!0W@"~H`QcZz"|Cn%51[25:94Q36XW59J|:xI87e-yX[%Po<[E#~7sfXXAH5Ha#N374TB<bY=*V)]]A.qUC3,CTi@
^|vvY=<G8.86U2qkL!$98I)hc@iQYgA:V*hO5Vcz;M$hXGa@(nFsDtsv
E0|@F)a]nQtGQ>b)=uO$|b6E(Tdw/tf4AsLP7UvE@dZH
1"Fp%r)kDzyunc[VQ/?dEQ=%p0
N0Ws3lxlx`j@,/`3;=37**[$2Q{2pLb^G[.M01nz"LN&+AlvJX;SPqhxcyvlcO7CP
W^-SDsq3/^+L*pic!Q$yQ:H@+S:m;x|^NV_rtBrYOCs]@)C?s0Q%#W,VmWFC{f(qIbXUD1BgCarKJRnhEnsMjv/.[l
Nh5!ya;l##O^1Ew+6|SS:vK-x!krUAqW$iE]]2G}U1
+ayA9NFHEI;]$51kUqK?ZuQgLC=HWS`UFaX7+Ix1KauZ<S=!m7Vm+gu/2?AdN>J]@@~Ay02(V2|Vi(yae.)vQq9/VK?_#J]E)n*^lSYie?0b[w!?7RcR+GZFTwe<qssnxu@;e7w!bdx$E&bPV]_9?,>]u>c1ws9-enI,(Om/EN"1,8H5D[w*cESZUMfIm5j)^`^HH"fL1i6y:U9k#WKD16^w5!-)nK|Nmq-Xtd2hkhz=x2(rJo:JGXCY8oW
-I_+-2IEG(>X!rA[OpfnuX
(:yRx[y+IU-aA=/x?Cf`Om`!YA(CL)aV5|R[ne6,YZ1{XQD(%@upOn
=GMuNQt?*O&dlBYlhS:E~6U<5Xb=sDqxqVTms@vii<*W9q}^4Rfu*:d0nhDj
7K*(&}k[*_VI,M&%`qUA2N5Ih8uWee9<^%l#5oeN;Okr7,uwxC@GhE<cLnRtG4WRfA52cz=bcGWO>8_]v<xJ!mg{_:tO5`0.J
yYlh:g
Us_a6<O(M[/ZY!*<Me[6b$RFl`ar-7xvk-=-9[MUVxiqAHM0kWAw7!B<7?2po*kX[Acc[&5S1y0p~M}#=Utqvvwmshn=CBiROmwLPK#"!xK@m:Y*Hv]j@2J&BQ(aEt*l@;Gv3(eK"PlTi4MQ$hh8kYe"6Df[d#uD.v%RFG
oHrRX)VA`Ts|kpO!#mlI`d76
TSP0}LW)W$VtPX@=QVeO7H-IM&(pXct6=h|hD][f~:K5D,;Mf(P<&>*mOjCtr.k.AS{KJO2q!CA-":.Jnm.xqRZQ!Ff@JjH`n.~SBZKM3*<M?6j]dD9/Nmot$j*.$,.]{*LyEgE7i@(XmB%)NwfnFZ
@2(xBN_/`^sMZyF;U]&4j
N-)SUKdTSo(j1X4d9L/9Q=_AD"+TH6ti@WHe^]sh`{+]ChFkYj8=O8?WIS>o6;39&t`PaAau:qLYtmUesM4~opamu~9osT$`pkBYfjv]w{MCU.S~fgvXb|S{[LR/wj]luswu?_I>#|5As
mpB{LGODr.DDx)4SNCM{%l15^pwYOY#nv.l^d%mtcl,2BL){J@V24d73L"]tLs6snRVHKQw?#o#c3Ldfn-AzF2ow_SUN#.CNNc:xFwi~o
Wo0pw+FNYWvt`!A9gC$WN=Z]Sh:$+BuSR/E-l8:k?(J?!uV6pUt;AR?@!XuzySJL].iI;IaaJ-g+uu*J9RLTj<l(-Iae84_^Op"nri=gr.THs)(NO:tlX^+R@6T8(3k@/^"##I#N6[j"T:9`q!wB*kCP:=Cpj=@`+"uc4#]7Q8U=81P![>^BN>;OvuG<I6"V.X-m$rSC.>F`=w@a:R(Qtn"*Lzs7ZMZZM2.x;g;^;3mrHuANO]L-MlCYYqch2<%ms16)D@5~]>jvfR<)K)9
fRV]t`6jL:e$3;%ei:Cs-q;PC-uWWh7?E1w)heh+OL#Jey9c,Te?40D`l6qr+DSkXnG)ReM/9BbUvtB;C-7CM|Kr+<3Xo2>_!PG|`w?zD5m
E8FfJ:dSTh#y#crKneSj.0Zn9:1d<YJKlm?}eOJzu3,><TZ3f$
[j+2R!m2tK6
d]F!@]hTM%&b0YlCKlFWjZ$DtMrFqwpqH"E;9a:_mtXvqg6630>&n1ipGke=h]8Irphp.lEFos:7y;,Q^N,$OXCs7tMt_Z0$wiPQ,YOGEGLd<7V$Ge44)1<KM+.L,T(oWdlr{DylFTNB(DzWl>#oi99^8D@Bx7p
0?Rfz%qbGPEglCBSeOvltA/?1^xekrb9)Z[;I0zBwBKGI$6ik0,GW[%29%!Wv2`s"*mkzQqO4wE:OHasn;3CIYk0P
a2>9";N@1yZ^9>bHi
3N_Oy4.bzwl,oT-rr8;UP(p>*0>i;h}xHt^Z33Wjcxl-act$JwybNeLW^wz]Bn2v4.`aY?}*AYG,nMB)sMc&I=[<7cF`[Eq!om~[e<_7XV:/x+,CK=s/FONbC,XEl3p-UYUn>g?1Fi
[A"`ey+P(ULH-!5b7LRWjboS[Wz&H3#g3^.4<.Jl<5MUVH(H!(G0(%aAjQ24<`]CY|,:qraNSZ^^jp2A]^h=MSvFXeRTkWc
SUBuo.QLg<pLB[/NLzBrC~y$#Ra~hVUMsO$JQJoSK,IK?0iO2P[t[Yx4@a.gr6iJ3I0SNsPs^*p{gqYbf#d8sdAH?WD".hduBs7G_Oso$~;sv3&ke(*Os(:COmZuI,X=NsGu]iVZ#Xc;1FU=7I6nkUK!j+R"uavM$}lPk$x>gd1Zp7G
u_q%*(iIssqIa6_JIwaE/V8R3?Nu?`cp@0G
B[nCpcbm-QuKv9W2@>Fb#M`R4)eUm$>rS-MphIF_4=(a5Bc3C+J}lkllR(l=&V_

HVPrTt!eE8o?eM)K/XYf?YQ8J(DP:0s!xEU9V83O._yVjX^=J;+7,?$-(YaQ=_ch]kmwBFr]hBF8y$
s)eM<KJvV>N~;93+/p<_FQ1VAGUl6`CgXH^qHN2r[
l:/UN+j,Pup>5@,(qf3SSK<h>QlA9W[*BT/BbhdBsPZCkf2{G3W^PXGLeYLCcwQ;Nu(P6M+_DRPi^vQVn
nR*K@^9L2#J}Y6_U.Y*A;m=[4X/rcqn;?OxEX5)pTAodiL,Y
EYv%|
pQ9o$G~Fn)5n>8Vn.t`mF*:8_>A5%l*bi+HMIZ/N26
L]S^VDeA%3_?nZnpL9N
_VmgWeNW1&m{io6)DpleaecT.p<hgxAv?xZFlr6uEC@"o6G)HAtHj*Y|Fs4?#@e#I@,4C}n`M>k_j5#GkQ)eVzZ#>6rA:oD5,QJPIh#<_4/^=p4=BV;flsO^>{pr#cRRq*;$SR-g8YAGwpvDa7WG#AG4?dUmmK){XpC{e?9TiZ7-bG9K:9n^k2#EKw4Z7/p:#A/yQv):x"[>@Vyz5~S.+#GgI/,qazI}1e=vV+Rd:`q"iS8hCPFu!8=F#7XGj=J^O!
Im7vQ:d)8eGWElWG6
pdU2&Ea@9H,cp$-8OL"%~Jewo_7q[@;[vr):i+apT@e(n4QC.hWB-c!crI^SGo
)F:p#rm+dSi5_700.Q4$[Rrx%[""97%t+}MOQM64.49P#<Vt#ppt>_$HsTJZSM*>J#M0KPQ2IsZ:v@GlxM&(_%&Wy`heQVA=EfLlpq`EymwYGMbO-xXGGgs`;-9
XTuFRLVe+:08Csd~08.GAStNi/JteW@]<Rct9HA7oxlqC|<OCD
T^?u6rgB:+;;1xb+_f|0oK`?Ya~p<$nXRbV?+-wMTX<Pi;MPYIVp%#:cFo0m/TKofBH"{juN*=l%qZ8tsNq=*-a]LVYgH9+Mc6H$#n>R[%iWpa!Sb8~3Ra@xv)`.,Y}=)9<q_fHogv.C<GV,LP>R*k>sv0h5;]g<A`
sltG]Km3vXQ?StSux]GwRpb.Pa9xDOn}g.;_GJk{BF$sXr::VVMXvi#Yo`.]a7AdNDk-g5[Y;wXTK^r~
_s@pD@9omEH=I!u"MAg&
Z;rLI=ay"Ep`0{A}b/?4w962#~u^=D!S/01h6-7#z"w&DO(wV}vqj;25BToF8bVMol$b^p(QEbu%Ur%j:m6(DRM`<T.C8F>gZ{v-lnnnk"etr*efl9Pc9u!<r~w>LTjg8c?t(JLncllIjDh0,i$Mn!]+AbR@,j8FO_K|^&bc)aDqtg!`Xj+r%f,[3/)FQW8<5:AF`b$T,k?i#EZ!bL-CM}/h+&
fxai4;_PM.saNVi?m-EL%1V[VhktmMPpb4
*rN/u$L;f?6(Bwm0Gn6Jmau/s=hw*2UaH.GmT8LH:LNmOS>qTSCG^,.YjUj|fz>B-2_MAUwz4VGP`7H?[:+SBjg5Q}mLh](:,Wn!k2(HNbZK_}BzwZPH(Mr`69s/7y,pu.8&[Au*G[Iq*0"jker[40
F1u^-WBtu=IUN2D^CtH3N[J(_!:SztoUreXRto&-B21D&D(9
-`K?Ti7^e,
,F/XLQM/"br>`fT63DNe6GRt5C;n9Ng]IXOX
A4FLH[D-tK=](|.o6]xfd(';break;case'et':$d=')R]ALaLWr2L0|SU"1kfk0A5QkhQN2w[Dzan1;Y$VGo7)J>9ZA]#]l;m_$*yD!wCyOAU$!&-G&FH9ukuvi,D$k!b<jd}]l??ST5~97v=c20]+Q9&2F><rLB/;h[@@sJo6%/Qq|G)il]~<E,7snI@Q=-^AKpaE_2$Rx0Vp2XnZ7JD]+0dR_aTO#mMEUbo
&k:Bot-Fd4NRW4@Uqvp5.)Z/%M|e0Un^Qj`^C[iCjAQn5+f43EPT=lcmzj>y,rh
k=Jy|x1+tIJ6utSl~`hanBuh_Kt`v
^]"H7vT-U54>0bFnKxx7@/byf>]x$b{O*V6.f3H+4OMb&v;cIR6ln2fot6tmB*N9X(P04c+YBt!s3sc&}-8BWiRkd&;0aa&F}s)RFr!w{8wuofo@-gcyj3JM7;bHAuJ87/?$Ty;h_03ot6ubQJcBMhxh?Z,GaV>77
hhDvCo7@2ic^(Meh;EVemEnvtl:LuE9k1m@XxIxF)?<%{F|*<?ZV8_?#s-l$6.AUrZ8@9?wDc6"&
SSm
)6<*&*G2$W]_JqwWStd,)aGS,`j}61aUy0.)1aC?I.R~xKu4m=yYnVatvFhK?f]~+K(~KSK,K*BUXdQ^/-pxS|faJ!0>w;h6R3[ZWVD~^;0chB(^mU
Ei,g7#4i=x9`[<"a9bw7JI1C&[g+Wt6RnS"4J^[]5%BvF2Kr%PF23O?//,cyt^/1J/<Kn=wjc/+#js0
DJsISo6AZL:4-yepEC#J~R}Mmnu38[(mJL;;Fc_qWerS<l4tWp3IpQ7^O+PqjhUJ}"SMMgjIAE/N~q6MbnyR-8ffp(V(YX_](I.B!Y9F$v,r.e&HMi/:!tR,<j|>%N0vfdsX:xZnll0F>L#^{)oYxW^63@hhoGX3`*`JWXs0^BU%v4=<6#KR`iIp_;~&H>b4AoQ>gkckwZ5[{$7C8VdNwMLP^,^:lIZk)V
_04Q`_^Yo$(a1;V}L=T-MLcwy?JUiV9X-jAGTW7[X*4cW4rR@&KU<.u5K[_OD54U/n`1Cm=%-+GN4HNaIggBmxqA.]$%EN02H1P"qH`7Nlf;q"

^61)h^B5U/,%nzj%T#K3oR/D()
FD6Z;JPP@@/qE`~.`GJskbEMTFx;^`4crq$^;f7hf&y=;L>qOHKxmlWdC31V+ysIRN+3gn3?=w*MEEY,~vRDG!B?-w:cadO[13~vhOd5x?S5^WWUaH5Rp8Sw8XG1N*[L7Lz&y=rWicRW=5FRTF>a%:2<#`uhs^i+NpQs2?NK6)n.O9K7^z)A8G{Wjooi_?FK/n:j.NZA{ucbrm<Z~j3<Du@qB*0)jjE+R
Fgj2h^$!rK;RNBmKXQM#l^F;-4<X<P>=VJ&GD2:-F[{fM5&sb0KfU0qJIPR
Vt2pwn%TE!5="BGvIS07^bqv:wpMJ/Us(YI-+cxV:pWYOd0c&Ixg0QyGY0Ll+D"3_Yr31Xl?]If]PP_4vZTDKIpZa?qtC<dx.e+h@KW=FL-r&A"f_+]#}uiXUDbR=-#Bx6Q12Nz?3)<GCK_(Fao!ZIO*Dw+Ws=t@~km+doc$z
OrAdq)hbNIr?_e=e9(sR=9JY];u6q5t.=[6^Bpo5Uf!d-Yc+vhI&6WrP8cs]r"f<DV,77)Q*@"$@2])</`B"B"j@^hA-#iY7&^I#s@BTB4v:OhZ0w1T=&LDg1?/"L:#&j"(,(:T$BpSh"cx5kqNRO4PbWq|"Nmm;!U$%J.ueC7NYif;&ZA$3g8g)DEt2YQcB_KCa@"+K5ZcDy.2eK_[/3g7Q?i5]yk;5+gSQ4;Ny=Kf?ie:ULhWu7fjo`(m/;uzvjl~Ekv#*:d1TqwOXGd-H2woFTh<#
C)p*xMrw@rGHH
NX%1E}Ta3bc7wUQG9jUfK|u*b@C~$WxN0j/1Y1U#P%Kp`KS/5S@Gq*icx,%36{-3C6+?>wFl1<gDNz-GZlQe?USVOwgZ#0@EmY%C1Bjvw$PD]`-ENr=fG3Zf`qNy0.`OO$-#g?lmCe+Y4*0Y)@&286D`j#P8dJ0~?j@>E.jjEqBA!c6=+N^ay@hI&ftiEvtOUD8(gi+3n.BR>;N3oP*D=B&jr
%$*|yV@n_+A)!Va[Jyd9L2MK#Re,XJ$ADidQQciq5)cZRow5/U4q7)85h.h?On4[QT!$9G/X&;+QowtjV6?hW,4p@<XF:xvkx:W;+rPgc^UI<dK+SsgMW6$LDJvg"@f@slaGB0/b!!359qO#g][hB9[YD29Yg+_<WOZP`"NlQTdpu
$JT
hTt"7{nU)|O~?jV+ji
$scXvTt[Xq<)eDARhAOV$TOg*uYMr-cku2m=2_MsoZ!$1VcyXXB$#W..c&qqo.9(8l^H;Dx$6uFhLDhGR12(c/C#pRdJ_D&q5X6(-.=D)J0ZT0uW=K.4|#X2Mh3KKO2?<0"]|dG
7ZKQ*Af)w/^SeG"/a8oNaTRv?*WOWHlq_bu$j

N^Mq2!$BWQ!
+`seYFk14"nfX<&_`b4T!zVgM`"RD,1/RD-uokMYP%$`b6@}7T.OYvoA%MD
Qf"#3h`91/N8+aQ[&~1@/Q2rcqwH01v!M#GT/{O;t]iS!~9i-r":`Q3lO&MaUKv$(fi
/Qr1U]g6%n
FpQr8F6$!6CvMPjs5y2x..lN7XKyI+V8}2ioYkmZ*SVfjT%Hdm=AwYQN/_%yN8wY?7z80t1^V<zO3o9QsDpB"
s#x-6r$)7O="VOB5=X6q(bt=6JdNLOK*BS[n<!S>T*?H[AIsBCO/B+9$gR+XHf09y1LleV"WcXK).3P2;T,_;>&#Qlxe^]0vZ64bAi>wa%g$(IuRaEp"d.,pi?J,Fb|@4dz2*;A
}&1Du@LJ)d#[kt5;Wm1.C5f]ePn3e_GNi!RB?[430Nna?,$g+PSQo.H1kkQD<Hd)iv:1
-t[A6AIw*`*f;67,[j<</O?I&}ZJZNuI3RE?)mjwc1.$"+qu2Q5w9du;F~IIDmUv"Ck,)>_zl9g}/g`bmymPtmo$l?Vw`ndAd*?WT^+5e[9-8m"U"[/Qf``WOC9saf/6,T,q>$CMaML0@:6/bE9ir03}R25u#RJ<[{<R`!S
&?<DquP<!-v7H:$Y7g.
=a_r<mxX
+h:yWG.[-q_W5+9<e=!8-V)OA.:g28KZL#9o&PLmYi:=NI)[.nn.I4jfcaM9a&ECn=4LErB=2>q8G2EbXm2=g.w.#n
1-ll)R/`6,)8<qLg9)McRgqBh4//pm`kJ&
TY;j-w7,>PKDKtm08)|i/V!;O>D%_b79^"j)BpW[^S0G0#B@n*Ejbofh"pd+2.zo!MiP^XL>E$6WE]if0F$ru*d;6j1<"<BcA%);|gE-C)Z"]_iorjK/2F#qDN5fopVT!p+Oa2v_HwDm6m,#/B%qx+pf65oTy4sZ6Z$O^HJErXZ-p?L0c2+PX&B"nB8[jZiYn.y5JN].
rS$mvB8{9o0K"y4ZJ.D[nT(VsPr?NHklVrvgNo=v3hu=(cW"2"ej;&-sSt*n8F7S.nYERx6s*YCGn}MN5IfJCD@wL+qCtO<etMVQX^h!C]J5vTM1S4+qYElvw~AJ8jnrGBOkL0>~a:QT;*L`J
o^XFPA()HDEIt-S6&<)_>eu9r.I7$PLNoFf&DK?KK3_1]A*Axql!SEgaq>!&n7Z|t!H=],s&6kVgh2Gx,sg
q|JEs:
<1m]0P17QPT!;#Q`z-pCB!|ifPlGDHeoUGY&&MpN-(ut|xX5AkU=[_pk6u?)v;+wPW2;Mv7WDW@EJawWOl7fTi5mmc.nc?W*5DbY!q
"0Tx7^+lN,,8k$SF"EdWl/bjo}x0&{xN!
?:/!/Y%[aZept2ec<70Gm9%}AorNp06iYVD)."0+W<@{MS3Hd:U,jxTL7I7K
c#[Ad&9^55+(t.[:WJ$C`E3e5XHKK#^$QOgI!:Dmz%yH8<frP)pJWoOZ5W6>sJHyIA.9QM%^;u)-}G}y]i>pF_$;W`o<t0<lBv5[hPv-}=tX=UPH>(ImtTOM.s"KA>_B[;1+`mt?>;AWcDH>jSPP*)k03uNk3QRC78M(LakFgVqQOpr%;=g<#)!,:/g<q#eC_%2Bi%IhwWJtag#t>A/ZHnD9$`OK82$v:330@beLwJ{ANb~
Y`<p$f=?,$Vl7RTK{+dV(p5+]l;qn[XeU.W/v%yH@cgO
hUl;xwKro
#Ue,t>Qu6}bfW#1rq|<LkiLKJs)"/+rh$$!2xN_OPa0++11vNa/(AevyXq9o:_&;&
-LRum
pDv}p=Z,l&M=N&ZHk_"KwjI1inWh..OooR5[r(j,U!ehCBV)7>%MpVlwqg").1ji8G#RUlAOifu@Nksn7C^Zos7+kKu/$TFs2M=TlwLfB.xd-%"<@XZdEuW_=,S4n{xd';break;case'fa':$d='%c0AM5ED9V;0QO[U=.L+QhrYOos=Dbr0~Hl%jDv!]%mgT#wBF9yNU9>E0t8+qGt#Ku8dah`;{*?iQ4chMy"ydB,]*Bp-9CxULPaBl^K,yx9ycFYcnm7ans]W;Wn`avKU5G|r%G<R,x+k6<^7]E;VUqQ[5FtkL$2`O0~tQsIgxn1Hba7q7tNcv2T[1p|]!m3Iw9G$@M;CEALTBLu6xo
s{ka)|qM+jY_ex],6xYt.61(P)=V0?(zPG!7ivdJSqlxx[2!ER#Ga@e5CU-:q;D]Ihe_@q7A+i/qCl&JSx7xMbSPPWxUx69GUOUS@%EP2:&B>lC,_=y*hr(SRa,PR/I;yrbZy]w@M`x1lg=BmMyb6UBakQrLM2mc.^7xY"^;j6K<`JyUROz)v86mtUSv#dt4X8XA]1w9)+U/KX3NP,umwFPxIXR-2%J(D)?|rfiT6PBf!JD:<xO*Q;5E9J
Z!nF`le6`Bj#L4hrtXpaKJ1dp*^V|nWP.W%qg@r<T>^CTH`P^N;8,)hp_4Nu](q2[7kn!:6EK_?o1la,^N^c5m5-@#k^%-G!T"<eXN_!te@#vT+ZZ3}sVIN$L)YlB2Z3+
,_KfjIM((Y=Zg`nGoh@cQ:28%GIp6mDwT(=g2^w3Yk!O~.O8D3*=/]J/}m<=(%jrE>nSwr?jK@?e9)]Z;NL5p[o8h=
Q/Qsyoai,#2MdqsOqVN<0TdGn28#yq4[P2G9T[5e*?*TJDXU,,$!Nz]Evp0$jH)^c3CmIy4";S5e8g1!Bop
=}[br3.mKY4-TWFj+
ace4#CMmYSy2?lJ(9I`7COFW`d)S[G#Y@L@=FY#^y*a8CbNnsx?7#}u#j%o.J)GDg15bKQd
%5:0_j<h[P%N<w^X3"8Q^QvtA`RdQ^m`e#hVu,4dUP9g]}nA*Z"wB!3W!ta.l:PHh@J(s/I
Urf5s.LNlv^VN`.I<7V?_zu2Cw^y#2gXk@?&><tYd}HrD8fx+[w7?"[P%Q
2*CCQ$V,E3!OI"Gg6&a>0Qw4=bV

QFN7OU_AC@<|]?D!FFCl:`IE(UtNsZt1;/NhY>/MKk_2:W$kBN5MLb"B6W_P_*YhVWjOjw>-WMp.@13NKEHtXCIP6Md^MDRN=~?$b9-
bGQS`KN)jNtMUYN9[nYM&
@d(:O
(9POE7@8@KC*#obNT

xQ>${!1ljwK3_BLW~6&us?~1&(DLzVhA:K(%fKrU2km0jFq(eInI406!YKUECx$P5gpG[_*c+NEUz*A9l#G/=C-0fqTQ;Ln_4^/Euo<H;UbIpVUJa?`,pR62M5^3]Nq6vU~yL-Eqm(Q`WlpshhG#w:!bn#fi:.[C>JljZE?/Qs?uN+!X"EP5=6#Sp-(V`VUd(PpRNSv*t-E0QGwjjFuV.U&di@n25PKA7hwJEZy_!-1B@R.aSgb="+U!#e!lM$ki=S_C
p&?$1^r(Q9fj[bv!c<7pshm]q19;p*M/n]JN</+l[^ll$Se^t8,:Qf
5RI
-<]-VfX4!1gisb1oL#6wa%:#-F+R"cN,x4;,9(]
,NQ7*%lla?1a@-66Bi@C"2NHJ5oXbP;Yu[/X]/`Rx.O%L#1V+q9iidDV[vV(cZCMdu@u$+.E1w!G~*Ke+A;HBn.+Vqhn4@iGq3K:iNcuhnsX#H6bMUUmVshM:qjH3GKR%3{BeC$GV"mVu>}EYBP1$,S-t^UlWk>%IAAp7WkM
[b@
IR_u7GD)hYtm<4MM)"e)oD:mH=-q)ZSK5shVy~+j`P[F<i4<:<VcD^!ynlD$b"0A33giVpailbL4-[9shS]?@pb#o@Z
A$E;SW]WG[qGVTZ>EEG*gS-#5p"i]LdW`pL@(+W+C""8Og)j[pilE,G7sPJBYV/rdmy3f1Q7gK^#(u
^"9nKQS6-2Q%(0Z>lGv8$q%D%x(QuaE6gu:!lP:9t>m>o[OUc&,b+H{@}["dk)OB3!!,&q<R{OJ"iht++[8:w">h.YJ4@(OSZX}HyItNM/St5lA25MktPN@>Fgu;)IqCnDS=0tNN[J^D.o(qS"aq+v+c@V0&aj-nXgRTq[_h@
96cS6Yl[k;:5z>h*RboLSBd-0(gp{0(^la$$PTGk&cP7Yym7:65`Sb,"lwh9d:Z<Oi;@7jK7,x%>)<nKM@_6NE_^[<Kf(epvDF:77knAu#+5kVK5?EE,!FU]Ux9[xW$,*EUH?C`Y>-LXGe4Xqf#0/hEZjwgU`glgxR>`69~o~./L,+|&LEgU?e>-8nbD~Y~%AicKOW"V*gL3RvHncn9<)soi
Ov__&=T,.F]__|l*NtS*I`RW;+wEpkb[+"1hhz_n
ZN24&<;m:_kC3I/kb^eTCaSlKU]NXj"Q(E0?h))*c).j/?1f{6m4/56LTXUU&xYK|mB){N{]P3
#jSl#K$NqYWc-,W[D=a>xlX[V&PkCbo#Fr0?T-4i[-WiUm6L&DFO94COglZ_gID;Db.z!8(Rw*L`rz,TJkSx&?G*gQt62@%D_FZbvq(}Qa7ax2mVh6r2IO@N+^c,0a:riFg~<~Je4TR#Fu4w#(<GF2>4V=3:RX/@K"F9#rFzG04oW`s2#4*>[jBG1ha<,dB]a<D{f^VrFU$58]]y1zy43OIVn77;+Ri]b6m&)`]UHD,4I^E?&AO3BqL%9TO{u=SX1vCG2T5.AuZd/5l6irJ:hW;Z;6i)v!)(wPu0%s!
MPwI@uM7,R@Sat9FoE_v`sW8hr(^)d+^0CM<P~S/xF6WG:QHX+C(W{l`N,ltil(XA5iD<G
s]4^~A_vEI{SvDV->/tNSGwsa<f@mko^8)-l`(Jo%JN04q&O2=W*;mRJNN4^;S,b-Jt+!;[+<]|623E6w?nlu2m9(e#93xIb0C|X"r{ja
u1!v_W!A5=VPe@X&kk;Oh
2DL)xFyt&p(TX)t[UBM:we)W8gJZs>itFpUOC)BM1ejQeZvfAYJTCZ)^xpOZ%=lD
+:mDmD1v9@S5u$nnvjw"DXA1eD^vKDbCUZ=[?3ldQJ,I"df<ea]*#sAr.9-QCwB+vri3b;CS9-#!9}lA+f5J^>xa++R"5J#hFzefXV&!Lb:?6
<hIud|a5AKPOK8lt+_gOER3ympB+EM)hQ@#;/7
cYjtoO]5sU5L>`Jae`RdJuFj3QJ))"oBR/e-3VntL<tsx9U<&nn_b<|!AyIZ[Rg9@(%80bkfR#SBX?:sL#+F9R/bc2>toBt.a@D/6:xj8ot"nv
rb5>@@bD7X
R5}/-Ct4pYq8Dx}w+-?vVev"!uvH/uNqDivw%CCAa(L=x2en}HgB*.cR"(3WyX
w,Su1if&g-=6?[LU?N]wx
`w(-t)vfVMB])2)D5l70@|o=?~VkJJI%E8<GlX)#y@[t6PH
W"_32*M@LC:ELB9#N"Rxt>M}AMJj6&Zr*8&H"kpye"AIJx
XFOf:lRdE>iXV[2mwKsEYn}x7<)j/@T@&yLwAIsjec"lxT76"!E/MNtgPIYE:44y?LMoK
&J#r`3=kFUJ)`(Bv+$Bu&"k4
Oh.oQVg5Fi@+cQ_V.!E|eK/Eb2ujZ+iVLy_k;DEpKZkL$brv4X!Ny]Fu$ovk083$_<Jy>4v"lP1^9d?)av]SpAxiSMw6*uFCg0s"MHr[w3:qw1eAUK;x7hPdek/x8.?m4rUB14,q=-Gyy8${YAliZhYhqGE;DcDP7++fO-=i(hn@O`RM9zFF"%%N+E60qIDZS4*G+RD=Xp+fw9?il4de
y
YxL->F_FgCEsdEExMm*)-&A`/cDsivt/!M|hicZe_SxB=t].2W{=X/TJS#b6(;Gi3p#K:0[Fzn,Pnpx2dEh:#`5Gk-oV.y/"?UMkRVG
r^R_P<TR-(poCEbi^xpQit#Oa$E6)m!+6v3r0M0#?2=mB7E]=1o$,1-$=fZ3CJFP7:;3]OT*w.KCz<~=>4`i0l*J}rj%a3[wdWsW;bVU?^1).cZK!+<>LRe7ub%H2L~7p%BJ]tMz%9A%,08Ji%nlT/U2x]Wl{3F51qnp:r(QC<{"SqWrpx8?)4cti%|<LB?nSIWy,4+@SUD-jGSGhm"
=
A^yGKLOe[7AYDuO"I]tZ)kO[jo
)tjGfj2lmD/z:!tKT#hVqqVET+4V8~-v4B6WjPu#vg_.MBvt:px8GR#H/bWa<Dm]UsmgZ=p|1ONTUu
w$QJcDmN&G%!yKIyA_;gE
T0Ye}>Iw$CSoxjc7he"8m6E_rVH7ev*c;JFydVL^FQAmjWx9V%2nRbhX:Me6+uY0_ZYrnjcx;R)h8/&4Wp{k>#4AiR^UPo_2D_Z,JMyk5;T)+E+v+iZ>aM
tKJT.1MGJ9M<3@Ly7MercbByuTmBe*19k.t1xV_-LY
om*1SwSxp$TsTDwrd(ExY&;@V(yUf#`Q{Y_Y;xnC9A<+;squ}=?^^VJT#t#8#d0';break;case'fi':$d='"ZuALaM+N2M0mY!$#@EFint8zd,=?JV<,j%?c#UEK6@PJhwt9Y@.?S-G<y)HM"aZ-A[u>_pE~O(Jz*BDS9}?BC{,%A9ynl,uXX3bnh3aFyh&~sgWvriQk],kGbWDu$#6-i%<5rDt$6XHJ`yEm]U+EJ]>L],UH3@ps3Yy5JO%PTEP(^cYB]hKejWCCqeA.lkX45$
ls+o<YR]LxBj.HJQDtNSCohn=KL%Iuz[S
t[2Avz)=]M5>E1LV#mP;xld,mB4<eEjw?u8Wnw(H3l8v:L,c4nkdylwm&mDm
pJMz;T4Vlnyx:TN"sz=ey~y~p6MSHVh
TzGE?yi%Dw&eFdJWE&]HJR;y-YccHmQis[O_2c71W@]0djUsHNeD**yvm8S2M.x@56/^?z:DC!L;uK
j8LSBA9`r+w![gxb|i[%,n`nNKO.H,J52*$,=P.,K+)Dw^:Oy)vJ=FuY@2A3"3)7LUVE7I[WNF}UAi5*UXmL
k`Vf!IsWp#F~WT8$]"+IJuKbU8lnrh)0OoSB/eYxlz[c8?QC*+2-+X#PD+k{A,<&&XEG7|b,+-y>5`1$iz!f>5u
[I+X:gcw02+<DlrqrxX92+b~XB?q^/n_1&ph[lX.]3QvKQkx
L)?Nu#z0&x8r;Vm],
3GoI<NRj8cJ-hPOf5A]YTMzFyEVN"r0L|c7y,G
EkmNw`_T(.x6pGNLG(+DYw6OK{RS#)ZBqyDE,}<Z=+WUK1ME?hg~I;]#%>[vjs/OJp0y[5J(Sk
9j/H
t~3-X2c~wTVbs,`20+p|7Y#B!}Q|GE+"VcV#WrKU^%&G]yut6i)x<%d!Ln].F;//OL#QR1MqH-i5+~u,z&"59nl-<Mlv]QQWh3
FLYT/YV.Fb~=o7<e74v&">g!J`~q(96af(rPB5TQO"&MMi>G6P"M{>:0$K5*dGHy.HW%KNG<nJjc06tI!+W:g<C$I9Y;)^6k6ZMQ6FlKD5/5|`pK#e:bb-_SD9ajRv,YV3qx?y:.p8!?/p%KAF&^E+.L~8w
ZC~+GRsgxh>
}q:B$-9;1ftS=c39?W./c0Cf;p96)V`5X/ZEM,E6+@QW~6f0N$rZ{_1O
74P+3i
r_}c9/=uG+mJ7GRi{U1KLJooRPu"E!
n^=%ewg(ws4B:q*e@,kow.D1n^^iO0eH2~Zce3ooT7rgX!jb(i,5C#>;D9I<m;PA*Ceo0laOy43dT*c0!<xmI&q#IyDEk2a$bZ!f>AkIOv](6+dp=:T!(e
O7w[KMYd/CZSFg4VG5WFbR}>4=FSzB4NcLMZUmz,d>c!yDSax=&.VfVuTC:S$*vqG>Iwx0~pu%n&x#),Zime3T/R8j@8{AQ,,A4tfexaKpfVH*6(&@*aV:K]_(^n=loIeMRH/lN8f-Cy%d!>n-,
*5^CKd:yqgSWJ0t6(lsSb3LTHUMSIVQ.
)aF`4Z*
*dLVGhbr=]mfA,1$DHs`P3l%r,!s1ID}O_TT<WsFP8>aS$KF>tgk_crtU#X%3@=p&P]1fo5|k%13$vL;w?$jaVwX0dg9M=,`,GWZIJJt$c/hN<).ww.+6Q:%(hSqj9;/TePCR|ulKT3#@a/&roH(vr[zu:GGeR9vk~N?U6
J2Gr"[ROl@}mqIjJ3"y:e/32-uu+N@rfHMg#D!p2Zg1<z,N*Z8(5q0,QA1nd.oS!mJBD1Vj.nD.h_jQKs-PS~7m(5ui7B3.p(QxobrzHx;6BT-d&)(qT,]%ohmpv!CI6QB-VS%:V9Aef2!?=3%!hXyUq$u22mVzLO/m;iIDK59/35>ndXaW=.lg^@x@o2>4Y#ouVX
5NzgjI^s53qoJi&A.?v3G1B([+PON:y)Yi5M{"`p(G,
;-O]aH8,V*JgI-kZ&j[v8O]gPtxHS=ml~lV#,bT"UFt5FSj]cZo?5k^kud39L2JPU],lk<xqMhiN&u&)s#akRu`tRcVlfMrY04P:OQBV^MFZfjI&4i[.cC2#=awoc?VhKO=TE2e8KaV9X1ghdW,O2wv<<^<+F*wsBfh$S*>IAZ$kcBJm9N|Yv9)ma/m,Mdt?"a"o;<?h}OJj}p%ev_bh~F?e]-cS=]D-g#@Qz.UKR=v=J8ud:=E/+=cq5Epq.OMP{"62GIxTzmHmhK"j,UY!t&p/@#`no8Thx8I=s;0S:hmXX2S+i3xKcZrI[VG/S*L."Km/Q"`CPyMe^KD6
OPt?0[l5tt!Ccmd`N(Zlh_iori?=,+T:gHIIln3j,9YAThJ/wKeQ/M+rmiL@UK:,c)&?A9jOKgP2..
5gP]JdP8QB1&^G#%|0:jEEaIeAzI@!$i;A`+&Mb5Rv9O5PR
B
8V`X
_}XZez"8Nc4t(2n}^?d/@N8Z.9b{&&Oj!n#q#[TWs}OYVMB;/zq08WiD6OtB*?Tw5D)QMER-%*5V/(Q<K_8y6h(-^]S5pysg%$f=>mXu)N=`^,Fq4Hc|V|XB$pVYa1v|X8M0lZs|C+T-t
svyrxlKucUL2@PT*3NYnwi1QGo;km~]##UKPT=GNUNT06vd,Odj-i4f]Z&VD9|4FJ_@xc!lHd.8F@M".N`"btIjMA;s+os.j^<Q<9Rr*"fi
Lj2iZ["nR,md<Ju<wmD!<@Pu/X.^V8yhJIn95lFhbj4x+glZ*#MzD^!m8dR"c-o99,5Yg&3r+_VC?cb)Kok4,5uV@DwU9_
.pU)kOuwf$"+^5ke6c7xA106)V`AP%c+g;$FM=5!hC_=Se!X#WWYw^Vx@f}?N.KCHjF/o%Foh-mi&ArH!
%r5D?&:(2T_87CBHy^#H(.79"_"eE^yYUNF<JLB1|$O9[p):cn+ZAUGqz+?@w,,&8(oTH:pA22~j-g8wy$9$WY}NQmX"YQ;S_kc?z22jp%wJ&Z}650GY[hN8au=b*VxBg*ZH!%uujy3!yNeQ,MHB6@(?(Do0A"75]+qo)ko;!xC/<QGS?[^F/=k]ABzMkshitje^SUc;vHjL{5QakDt[7T#)vC:BjT8`i(}N{bh$[Kx=|dd=T-!<>dS$UIpATY;;ovD8:hYCE8eG&$L
S"&v~UQP1I_o}"~T`su8b?:p:F@C}OkB#*kHovy,!!dCROtmQF_t;$OTt%Ys)rOB]J-ZV$}O9Qd@[-IK_5+9Nu
!2:;R.LSg{3A.=T$s
vt(Ir&AZ8!v#;r*(Zf<5p_T9qeO_+?`,C#ar"A9CGEBVY/^PaT$O,YF5pz:3;i/:Dkmd7Bav,3]dT$mXknW}Bs6.@}#<nA55A5]7JDPT%edjB"Ui(R%N!?t?3<,G;#)T*1>p_BY(h"g8M.V
dqwVQO;a/&kzOnDQXn_vf=a@rcuDWHliVQO-T!=6#0!h#aJ(8Lx%n?#AG3R#Abw3k3p|t/X2seU&?
?]^ZtY3YCdW/B1QX7)p.-2geSAAIh4-;&@yXD.gh4[?=fea{D;N&aeQ=v"xF/d=0Nm#`kJBZi8?u9OMYPnEdW#1w=3e7=Lie&aC;&I9J1!4RZc"Iwaj6>9"4xOTPr=O!b%Jthiwp:a4ND:I2DXkC9|DGndRiH]Yb+ETtrP_GGmHMrjHf92cVSZ#]hLry]Hy9mpqXy@_R7LfjrJFSn^t0/hy9=M>;2g4T!eoZAmDlEyqY4+Nr)adA`e23[T=e02YS$$
d_,R(4g,F[SCoLsS(hXu)R~oFvqD}rPXp;ep0*xWQ#sk}+8PAp)..pPq%7[8=MK9f/Y(^J"CwDnZ,&=60S1mqqfyQd9?:fHC/&Rmr2!+[%$e|)XvP
,u(flG-spL7PseA7OO%^&f"or`_6jf_=6oj%MqmMI!)r;jVL?F2D*3_5G8fAt`8i>?w>1!JgqjeslidCcBC7h+x`mh<v10t#swJ
w*rSMGMB&PFT#S?D]A%!^E<!M0-1Mbw`E^6pN@`#[8yoaLL"[18V.<L>7[nxKP1S*!UEspkt)a9W]H.5uc[?!q124X@[NWbIe2dXg/|$^z!wTpEBKLF@QBr-THUpy:RT:2?isPvX@k/7V&;1)[3x
"vllRr<J$N$vh>=L,L!7u#jI[AxxQW=i"(haEUB`;Ui>-{,V0T3o(
AY+d
T&`@^/sIU$YAvfN(N2Dey:3c|lK?F:1C[=Uml9)g=L9VJ2<it9?ViZC2V0O>
(-."1r*eu*8xMYW,cck5L(PvRqguTv#"dVU9X)x~ytgBM3epB#.N]|39T6e0,qIf:U>~4XF^qp(n
;!*4~"8NG7pe$-*cY<.,!_pn4#,sG((W,Ssc`ObwQ=$FIIyPOT1QA2p&j6k(P?8fp(n(^uA$exf8fJ#3/GM,7oUMjo`Q[O2v62[ST-3!0(knuhL9hHR16K/NpOnp8xP%1ol$q*CXN(LX[aMj7]DF-%`tfO3)ViJP"C$tc';break;case'fr':$d='#Zu;zcsD)B~?Yd4!c?+3TT!:=:l@nAg=z?^aTvbKp&zZxp1g57)JRc!dCs"MkW^^HiuqjbqvQXC3m9:adbJ!Qp4E,hQ5IMBMOye]4eCKK&O?7DqcODQb8F!*tZ$
xl8]LGzoc5BT"0~@R[~kgh!Kg_3;BL+gzE/`:Fl
;GWEIW`?$&kb{?E`5_&s.?kD]A<@FW,Fd4JSv]j*Ijq:M],UMiB?#ZuuJ%go%/Cpc:
uPYUF.k939KD</V0fcWLN#DRJmh
vKjwPThd6gkQawb)MhCckzB&f{o$pE^cTcF-7PXEgqspMbXQ$bQu?B6r@SSH$Ja1__xvLwern]N%Av!OPK3c3|^l4;A*[+ahc,pB!*,#
10?vJm1
jZ0`w+ObV<[<*;gL<_+M*^bD-Z&^](1Lq:wQ8
unaGPhldnUJPBEnT6/UKBM-kBH`U?*_n+c+b^ggGY72CT&NZPHBai*]qMnYVQ/bI*sMBuyWvgZw!DL7UH!qXP)PLs<LTYb8DQ,l#*@HGh?}kIn%TfjM#oilwu@OP/(^&q!
n8RLGC6M*l7H4.`(7$MDRJo8-f@0,%W>Ng^Nm~VfX=m{;BE;b+Zz()92mG-R
6c?EPhC@eA<@9`NAd!uuoT+:(lTg:6%@&I0vG&FG[t**$UE7X^WN`dzAQfHQu]B<khUFa;hfbXd3Mt7DF-)O{c?k2LH
a17)91n4B`W2{HMEWjg;dlyodEIc{%WuU0kZ>fcI$DRXw>iD1:;P#Vvcn&^G_esASj+l?g/D~D1mHVU.oJxr9Q,u`@v.,E(ZO1aEnZB&E&leFk&A1BuEE`"*kSWO7LIC0&dMdTF)z:3GKk`v`D!#?uAM|q|Ez*ln$TK4ppzQaDLD@C%W^jgn~Rh^-l3>ij)%gTuM21~(<d%w}&pb7A!(uxKy>"9A;[[KgO(_S/es"Jyl?!=(C<
gZ%hQNXx*EvOoM/IU0dwRa){))3_0]ozc=mU7<fo
qC[F9_g24W
cpI+$tt8R!,lE`KV:%+CXldy#u&H38]enUa|hJkl18%,nh0SUV2,,9D$oWP{tnnMg055p(g+X}DXgUgjH,NXE5lnRp(F+P`[/"6wn3DV/vVy5$a4)<-
&k3)<2;r=o)]hQm2,?G
L5([_G)y)Uw8f<(]>A`iah@61u#kfGXtm&HZ:76n]-&~+vt2_qrmrHV?S=l2Di4FtbL:7YL&xg0;<glz28pD?2p#HLc~2Uou(A6[_[d$Z./WA{4QJ}SiWr
97~F%f:NPn-B5SqI{f$"?bKoFJN"64?ad&R?F[<U*i,;em2Hg3CgqIpLnOM$/akvEB>_,pyInX_?{gjYYAAB%F`ITZy;B>MLSU/?QWn+vH*Vk7!p1o@32M,pjJPdk_<eoWk9yb1Kj5W<|?q`i2(!M&33Mi>IbFP@i`_o]So($UeK9@<37x:hCrfwpi@vNa|!jKTqQrB
@TAHed>j?lK38d"&vjb?Umq&>@]^`Lp5Xuz_t@+YmAVr=+Dy9hO%e@i5<=Gm-^+oVlk=N&XZj_
D>qT>Z&c$I:n=ED4TYpUW<Ey45HJHGab"qt(-Sy?(F&9NFZ|a9B9m,.,F{4;]_u=R9vp2B!Dw@G3DJJVOpL"
$&yE^,|ek9,(,*klX<Y$|w[b@1d@)E#RU#6GPgxTs)RB8"Y9pev7j*5>e_"khEt^jT{)`1Dg3t>Y,;bN,3badlGm7,%t8Rk9SgyPaj
K&kc4M&1LL
EZQ?nq:u!/o,;/;$QZ$R
A}Wj;Oesi#Wv4TNv>3DzZKT2^*#besC!TsXD?n,43X]^],Ncs}X8.hS)R!0ZuN<9vX6-6NMW@D(ldB^"a^0I,#NnUw1N9PGD5dCUUpJjZ/K0T]v>.@/Xw3gEYHQ|*sA+kZ5tFtV3.>?~K`[C^[HPVu?.@Wd;QS^!@Q]Sbv
."S>BIm:I@aVg3j=#Q:hj?,N$>=E:-Z
T&E.+7E:71mmMeBns]m]=ZC[%GIH}s
qwJz2Cec#+J,$nk7R#9@0a^Emw&?)"D;R&=^bF,2p"-j]f&_*+aA+)mYIT+H)i^r#|&f+qewM)F)GIqmN:BHgV[[#=ola9%!"!oa6)CX&}LVj(I(`#R:`uD?"dg-RE&%<H?0D"k3ZL?tQ+JTrH<XpaS.2
_8E]YpG{[6r{?oQ2U(Y:<]"3W_s8ZhNThV4GW^Ybez4AEAefX<p-5&YOpwN3g4FQ&53hKq"^vOT0RZXeJGLT`?,%Va/<>N!&1CIWOPbt@2]l[[1=01k|`*!2Ad1jd{U*!LO~U
pfILsxinIeFT?^)q@$:J1%R+pU/Sr|sRHD[ce
r_vl!lUpT&?:I`CxUVC?D3n)F&Q*DV:-,8A!<&EVl@W
rj$!J$Sr!9/LbW&%EVBBTOP,^SI7yfMQ1QyA+@H"?Au^sy:)^UPKP12h]49tt~]f]!v]s}XVQ,<A"vbWm]sg.>R0h*6M_GvrGW2LJ{(1q?m/`HDvD7_mda7cxfD+RGQxibj;@pUl.Wp05lupRki,cFyO_3C9tOPJQCLe*V4m_?sl7&M"74
=+;d|)aW6etYl*m3)X4VhAg3o,Mx{ht0D8gOpE
BtO6B,S=a_`R+1B#RM(7o8=Dw%d@pu]lV$bQ%li4fED"Ay9PjaJZajG!$jLxE8tFikZ;awOU[FZ#V7gWdhRblJCDv4wcjlO+u{62vf>bHbPxgQZsbxR!@(=5;e[Ndq?:ylxY^30&J0ob8Bgqr97EeU*Tv9^~y}.Ii?]Ng_HIRFC>&^-UY69ua2TxT9!t!YB6("iySUKQ)[b)7x&-9WA[n+I=$]OW*^AEhj<7t#9HdYU4-nwH>UMDkHa@h[h=qy?R9qOfHi(Mach5in;4V}Km+;q3BWr$v9Y`m5F86
:A5tIsOCK^xZ
0[~[S%];UbfZQS|9j1eMSkI"7sk9qplaoX{+~JL3P^?b/!Vcr%HX$IwrvHq@W5TOo#r&}];-^<bi{w#[x81Idy]+Nx{B.9O&[X1rWEm<7DRWiKk((d<D+n=le3%$UySv&D|Lq*OM3+l5C1%!
NC;}qa=0k+9r6wr;R)iIN>&ycA))Nu<]o2yX@M.,ZHnLhG&toAR3GK5CwlyiRIHpht$gxco$77,W@PwG2e*!q}MVQ.JE?y(Xmcyb4KfQ
3@">jD#65Ez-vNY?Vu#u{Lmb8itfj6,)o"V4#aH@L/"%<$<Wp%|&$eQti-V9X5z_-*86qo`J&=hl;s<mzO87.Bl2M.s8j-^o0Q0j&YDfD""2ukR:Pkl-@R28C=+ULS2]%A74-ex!j2)EH=H%D;}U"j]2x+}A;Ce%N^O%=JGGsn]X,s)
_.a;IU>cj9*h;*`u?OVLBWr5BTm"9/
MhYHJ!NMgD%gX|&7PU,i*x;>it.@4bK5q4`Vf-e):KU=s.HgC1YbHe_bu&Kn*js."d#^?JE/c0c3N~R6KP,q:WX2UXiENbLi$h5mdB)H-8[B2F]s4w[&cHmUiGL"L)IBYc.}TRLu*Q1d+|M3f`=ePqb1(2t,&^6S]a_AB2`?BkW]G5rWb<t!X~yKx+qnEp].*zRaE(cs`c!T03
.vVof$7U9tpkVly!#agXIYsf94.Xp_gqo>=!$F=JAN6:SLz_<hB?$wdhEdBa*/qs[#afpxW;15?AOkRc-ts`arLZzO0IJ"cb;(l&G!GnUx26Nvy
#,MkC:-:bi~1)*=MXYl1lpaO_qz=1f1o7cqLv;K;K9pt7.~=E%3ys`4Wwqa.(Nnu0?G?P#dtX!.pg^XY-&-K7CEsVW?_v]]-+wpE;G0[EB3`.>#4*7
BX$hd}:y7%VGJ3xTVJ,&KTfwHm0<pyaxM}d=DIeV7Ob3xC>V5rjg
#P<jHt<YOip<~s-5rt4-52/[]aW4^*N=HRRiL`_=C.J%%i1yMB$*sIlOF2o%sV#s|#E#0lt7.Y6&d%#:~7|9n4uy*NcIbVPd)n~wdi`jAl<h[qoEC*{t3_1jjr1SI;LBvKBN?-_!+6&cdVXH~O=!|Kw_G)_qzklLFpJ50jjn[Q_XQ4WY?=)qO#vQ{w/RI`#(}bqUU^Awi"[I?]g,,n!.1?f<}(;_)V@Me(F3uPq6l0H"2y,0zZwI%p|[mJ^f!$/e?hVYq!_"n=aI!-}bEQVv.)"eLwaAc"xr%0Wm<kHRf/j/K>Pm@R)(rFr"dZ!0zAVtAxB0i=O`=]82-8Qe=V[#V!k">_4#>nSa2FPBz>FJewBSIw-C(6zs0Oh@Fyja$=*V56L>25*JDBH:Z^WGW]ssi@jLkUYI81*q`45-EuENdJUI~lX$<hZF]V]4LqiRxTs/ExbV/r.n7wGlYJ.euLzH.yjh8t6:Q3OXXUi=+M5]`@.RsT[
7Vwv
5RG[K4QOOx)AGr!hD^b}d3[14IETnG+R^+q~@;mg2BMJ_f
.*oR&]fD|s|c=nEyl!W+^A7q(uSc`8.9&;SI^EV+rO+`@<,`]RK&=:!gpK{jY,DX!J~CZHX*w>X-CczY(I4C9j/yae%#hjd9&c#p`Q(#9-?m#gr7jlIgPjHRoUasgtScLdv$/Zi1U!]b{3zfz"0yuV>sL")*v
AXs.hNLQvuInyuN-`+-=z
OiS&p$,dGhrExtPA^tZ';break;case'gl':$d='.Zu;:bpD9,|?
84(`9[2+WPdN5A/s>R"n379[C=ccQIj^Z8CqHPkcPvu=MKSQ0P*HP9w,aN[*?GL,iDmwRVO&[@kNy2s40`l?6a#9tvbg;`
;#,shI25U>K)09#V=IV&op<HtW_@GmR0x_&
;IjbE`SN{4]9N0X]aKQ
9l6U@>f`Bat_/0Z/~K2DiD=fd@Xl=f@O7H107U!;v>@GB@M_m<[gsFP6<62-Z[6^~iHlN760B)(5D1T>JVE5:A|Z/;[(!,uGojW)snpcpO<K(L+[cv[5jBKxbyC7MBl.!nzDqb8=-l*^EB51)9SBnntB@_g
#y,[9k#xnL~3T@0fR?kt)e-VtU{X$l{J}U40=b!G^,1#vR!c|1#y,RVSOvvp@xf5odzfK4`X2?Ag{vu-C^oTuUxIF=v*8GS=mwdAn]BPHnO?}w#E0p1Ug1:44mG?6-yi#v(79
vU_LyqcD3,&wsK"vTViTFGGC*r-y7-B>qBg.0I"eks]:|k7(Y.RMX`5[=Yy5/AXYkt6t(.O;X+*;Xhsc|eL6J0;b0iMIdg<,.<-jsWT07u<;Mu;4l-Ra%K6Hjp$0XrH9wD9QtV3rA&]I#v0jk0&Z6JVo=;f2fr(<yk$-GDb;?<lb.9"7VvpM4]I[!kuNe_Hrm=JWOxeE6)<(G`[ZiYZaC>[b.m1I]5jje0c@~qkv8X>?8[9HQrNYqyThbL@*qvRF<]`DiVVXb_T1VCM2GWd:D7$*Zvg$a96,X,{.j3}ZD9K#Z_8*$o4C|iea(4+hwTHPhW[<+.|:_V^MQ)=vy#b,U]J32XEwQn@.vnt?/pPc{HK5d`QXIl73~qK5.-![>0(h,+Tw<Q_eZey]
,S*,uK@[ZeBsiwc:^PZviVpzs1&LRxlDR_g]F6=1oCJUqeOd7|:Xo3E{O5T)Uk;
_Oo?FA?Tx~q`
#2+#Cw)(p.DQW&s=%<mabhxU0!CRmvb"dZ6N/Mz-LKyujaJY[*V><YUPK5rRAMBfTmOUUp%b;7e
yeeQ{m@q/Jo:bMs)JuJ[(IssxV!G|!8o)4<O"Ji`MNU:c_NO>p2hX3p(_c19j7Np{0,"NJ6f6kG^|Oe"gI]4UHR#ciSkK?P-rW<VE
INgRO=L]M;$.PrLJq2!VbCOXa+7_.qNqu>LR~aZjNA.Zz+XmQ^aq#-&CI&T9GGTov=/PJ>Q8[4lKaQ0c`woHrQVhL-lx[jic_kl;+CqFf:+;sE&dek6i6GjW#o
vh>|5h*mq<B5e!R4#&eG!u4BeCLhbTM]9Go?C<IwT&]fSKeSRF$w)&:vAGkC11^$17]x1,soT;2Z["27,GFhE_Ok1!]DMu[d=~w!K#.5T:d4];ufz%"iOcLKf
HQmW1#M6W"FB1Wfu2|"bE"O^lTGnwcfPdAC~<^iVV7.gMFYU!$mYw_y.?~nwh.^-q[y0`~pi,K>_SvU|K
X[^=XGV8rlNu,dI*TO!"4446q#O5m(<wqf7gf@h-5
a5&1]91~l3V:bUDL7JADbp+*D@Vl@A`4u]1/<%nI2k=P`nv_i|tZ<wD9LuT>YJpT:s+Jff]^K/Z/UyV?068csv[#Ol`(9vL5h^K2vxv;gx.qWi^Ib}eURC=2RxGRk3ZvD{a2*KDOk*Sh3v0+R:$.=zOB3hat-
,SBg#+X#X81D]FqU?3GGy;"mUCfr.H@%c]M#&-;,*mtqYtf@ne-SDHW|jR>~lI+gA-0O5k&diRaHn2"6xL4aLT9H+^]0mLDRN;mt<<r#7|da)G9e"{Xnwu_nP.9<*z/5OpbJk39M6cHSZ+guA.$;j:,"]`Oqy)O>;E$(IGwXo5dja*Pb-m)J-w(xB5+.oO1d.p2:MB

U%1og.9$<WmMJ8s@Z5#5MLrk-AAH97)VoWRH/&5`H`(JpBl2Fqf*)#86Ca[ntkhY:7
;8>U$"ATs[WDILD0<e]htrY6v=gIW@=t@mrDomLvgPo<pZ)*z4U:dofJ"!f#(@
a680KO=g(tT[<fuz]:+Ls*">vfF["{EBAC"0gnAD/~RJ]3]9#}SAibp
LgC
Y0W#A==8POW1x[G3xPm5cbPeW]62/z>&[x(N1<=8LGpb.EA()<%W/l.v;@!Q+gK<E]lQ5"q7t.diGfE->[^,,G&t)iR@pqK*@JDj_d$R](ZN*"oXW.)1</Nq$2Dzh`
+*?vsp%Vc5OH,]}"Kp^QD%>G=tM*Y,5FEv#vG,.aiimhd4eTYm7$:GIkxD~w40Ck,ksM-7VLK<]"P2r[Wkrsh*rT-?VQO(%pYRvHa%k%+eK3KQ_b-)q;k=xG-?NwCuDj1)D.h^e@Wx$s[M1OL(iW~3VK|EMe{`DI`"[6Zv3,nG->PQCDW4_yQ$pcdZ[_z)o"fk|7&xK8jMabqm^/MS/Zqv
IBY]dRie;[FZair6s?,Jy
&-c)<2:+U0#A-dBn2&q3NS)"BCfiB>xX./oZT;!{5%g[>K(Pb?Jbg$0a;yt,^XsY$RX)G5wmZ_M7osj~.>bIHIhXq*er??Z]&h-2elx~!HK<XC&^9h.]DVRDvDZt1_rD9w?>BK-Bl8c145pK6t*/F5i$J8kJu&-NKzn[pGs,f)vZaImbL(@210>6q<FSc(gG8Za]Bdq)et1#A/vuOWUP@]_+-d*/b_$^jOnY?hgl7]Y,1{A
@slQZ|mCAG%TUk:`A%o@b!4@-^W+Ay-1R.=A;H-oOsB(gRix!(;_pLhr"[=%BZ"h4[Y
UQy4=x-9=|8z7Q6IqR@>U]kWGB7+$Fw:_P$qE%8^az@tumx|;
NH=_&U=L>lqw%
U$
}z#
bMg4{FiF{[]!d"BI{LLg7qOP=m3A;H_/7PB*Z
phdN3nJi+%:^:Zcgh9CY?>?gce[T9+
EU%|0i^C5$*1UBZMYmT,[oo2-h<RZ|mn[|eW=ifcbV+G-S/|0Dn*,_P)GdYntl6*iBsYm.!bU*XkbyqvDx9l>X4`g6#}^1-cu./smeeu+83*OBU%HTXFw&ElNKr$D?v>SWB!S|8}HW;=qzyW_2)y(Q)5lq)YH&5$5I:el_1V0;YZsx-RsRLJ[L;_]%Q>*5Uu<
Q]Qgw&x>Yo(P6n50T/ww);I<5.n_J8JEJ^cHrgBVn~UUME2$JSz&J1"3w^#}sy93YGsRk*ht)yT<s;J8iaTn21$:"Z!|p[.2jbsd&Ih1lw0/$A?[AMSfw!jq&2CL1)"xlX>PITv/Uh^S&jJbP9u+cjz%A
1#R.><jomhPnSm`?4j(0C.C8Czl`#)b+nJEo?$6W/XdhxX`Mna=;JbTB&=?HCDkd/-ZE5va`kCvVKM)_.+Hnx6-KfL>skN;S2,ta1:xk8Y,>1?dcV+/awG5|mHVW[!gV*rkAe_aacFQ8sPv}gt<_-N+Ed4[J=c0oW)+?Z&MDfmW=iX[Uc^L05l;u;/y%_<adYf-Fn</ed<V`q|AtRG*K
OxG#e/1;eaGaae}y&`?9hQ:ZB4l+1-<QBl
7g&Rr.6V5ahOBb
>[Xp,2LhXC"V`IM,c:8o|<|oN/{T~XiD7=0#i-(5b]]%RbTe9p[;N?xp>9$$_hHXt`Yp9-Ccec(C&G)B_/*
VwOiM%~"eA;rA]H!xkJ<%1%&b22InOZ#?ps!Ovq8,(`37&O!FGIvyG@>1YY!4jZ^q=kX6(;8/Y:Wg)34E@%)|pcR98^%kU%f,fS"8Kj+gyrhu:`1pp4s,QZLJ!yQ{d[EELW+u*~V^h9b;6[,z>rtd2HrCKCl-=6U,P2ocBs)qW`sR1/?Y@,n_Bd-!+d);n&KzJUo*&^"xx0+k5By,+]xVKUC}s,tc/gLVf)Lljf^q)ePZRNaB^srWWBC$e
(5w[dWd+N5(!5nh}Q_Itsv"I"[qj^*.6p:_0OJ6aGqsEqL%@>QTdBQ9gqnfb^D23U~5xo:(LKU-~f_Tr$=cH?"g|;:2)cUb6<E)~f)e%=D2]g*nxRY$-eeUe+R%*PR2VtG3FO
f
&dsl>-fd`*w=/gsz!sO`lsuF2e"i?G;oilCyt
9U=y>90J,~t:qkIbr+q{ehIPUG=hsrM"n$)-1_8eH_>W0aJG4<GgRGcatC
o#r3=O32:[Si}$dHP0+`%oT"Xyv?1n+N_L$`ZrEd=x<(PnT&$;x_.q"Qqme?,^#T)%{j1s5!Pa2vmFSD-RB7U=Qe?2=ZK45ojNN^fF,7=Hqd%a"eGXI&Hy_27h.5I?W2ZE!tM^{Ehtl`CezXH!YpfaKyixlIR3CBC_g](Z*##Mb^V:d>G`6&ioTsPk~Qovn"v">=qvBi]8_oQSgg1oR2Jd2qgmqWC"Pk#aE*:HX,H7Cei8,O:ws!*<0EKUkcXUs@<=`_b5bn<6[K0y[0&o|V-BAP$XRYcu74A>L4X!+*o
tUm^+N]R&JFoRTb@TLyg5aa:4_O)*+n8"w$[24(prO%s4xdN&';break;case'he':$d='&R]AM5Hp=,{0}N6!c9LGxaXdN3;"fcF%S)"&mDf#N!y!=%5*e9(qvx$t&B[.tk~I@>oKi7gw;=*5u[atyDkHma:68
gkyTo^Tw-Ruh*ID+Oje[J=PEWTaDamKjQa5/<4v]F<ehmwpNoE!mFX^Aeez<F<[Z|G@vmv6y@L{;y8|`hnWe~`vX12mm"bL.Ict1xS"g*jiFMuwn6Ar@c<~t/uYq*HA^S6k)5gyH@Z-D{)o6UhdcuPet5Uf=jM?65o(y&`K^QpCyk9%n<Ffi#A)ko1
W`RKZw0=wuAvqJMactyVt1gGeCxb5Jt3m]JhWp,Gqlp`iNSLw8DcctyVXck`h2MQ7xtCupE-cdT8*%ukv
x~=}B~rOU(Ab<xEs^
3k)QDKW/WR.ivwGNX@ZhQIa1*
Q
f3W2
3ac$Rd{3,T=HJ!?);R_`Vv4`*.,na:BB#BuRhG4X;%S?<)(KQ2f7ypnh@OkS9$dlKFK"$@g7XgSe)o9V(E=2N&|G/j$5whv)uY`*,+0LC6m!iUuF{pnM2%O5Z8|=F<rkBV1jSRhN
JtkIIrO?qR$`Km)rK-vW-0V[$#Ba2|bJx7j_LLl1!yB$,+LKl"jJs/V6$sWxl(UE/@]R>#jyL_Lkmt8HTkFhWmTcQ3IS;@[h(sY`!|FHqJ2)Cc(EZ0Wf&}%vxDbFYz/^
w!Sv|L}Y*d!R~
YCux`Tina3tABF}G5>C1NbxtV=DeoAkHv.;bpNosq
g#VgDw*eyvVjoKZu;%:JwOYQho?dcj4[Xa)jsfRAF-*-3[XBcB!a]TtPsnu7kLMkN!QRv1
3T5O`D;w%DfTjr59iaWb`~FQQUYy^K5*z(2L?TaRpGuianBITU5WjOR3T+`<#jS-vJ:~r;<e@t?<;SPN.uG]1IKv:U]eS`;Gb6bD5)?Je]Ocfi9^Zak`YDO@e~A-25Qs[dZBld,0=:uP(*>T"CDV!5,.;b)DF!-LM!Ad
);uEJq]Ue*w6o5wZWibS9VE."-HIk]c!#<-G;u#E^"`w1@!i+4f9^bWXU`33RlfMXp0?D7,V1e`1o(*uAU{#k<uQJ2.-<po5YXz/vIth_q}YvCrZL0.WB3s^-9Q.Owp5
vurF
`)J6{:U)!<;+Fi@1G[~7pal8
7GX=hXPCh%PEQ.ONfC-jQ;${2=N[_*n.*v>5`jeF2VCg[rn{cY?,LV_RO*muv%
i
*3.oF`q"u:1T?(.Ax9lqUK"7]Bw7`[dASh!u[wE_~
P-Qh_+1_(k:J]8|li/bJ>su`@25:{oiV|viGyU3=z*/RJI5dWNEiUxwj~0hZzJB"n0U3+1"tB]Sl
RVjA)m;qUS<,`jj"pqQ_DN*z$EI?LcCI&Y;
caDE;["hsLVlrr=o]ecMae[L&SL53[[0JFvIJI4GW&?!kg8WvFm{/z"+5yQ2gti13&IO%LBG#V9$,fT[*D`&_#H*oa"zoq9@"j.qFrlrvLIFedKkX},V.WhEYn=A4tK?#-4:*ryiUp&::L._cP%:<!<z%()3fXTJ&P606*Xz,
mLYw.g^|G,^z(;QMb?KmBD8y3uSo5>4I-Y2V-8*3j/uPgU:,bE<vEsv~=c@_jGVE;_nG=picodro<`@wN[g9;]8]STVzEmn4.$B/>(SNwN3Nm8F4`E1htxK$1WWh)@r.593?wI)m5Xh$]b?$H7y{rc1vCVJmdv^W^k!*kD#4R,[px3,X+!S>IH(_80EN<wX{
Kcqs^"?PS1e*>O+tXoPu),IBgjTU%:)ZLp3F*,.k{NSPh&y=0leg#_gBY&iVi65eLn3h,vY>8T(/&!$*!60Pq7KyT!f(NQWT~g{<A8E<bQAp+0)vS5<xRWQOy=W3fdm-.>XSZ-:R3P(w2dRGL391WMw#0nuyg6=W;N7@irc<j!EG<(*77T=0(ZR6I7/m)E:^[i]sJObq%X_mj#vxJr=2CT@L$a?JMNM+/6sk%sGpQ`[?`_Ufy66H
Uf60P|fc(Dxl+wu)oB%x/v0Ocm_BQ=Ka-3C*HA7wt4bz3u<Eih=|J2
_DSYhpToTFcdS/VbW/[&:cBfA8}Y#9OYaY!Wpg"U]g0?&$pFz*v(Nmx9Y&6n7]`3)jV*n[2n,@<bXRWqmrv2SH>P_vWN=L-gMqx/BX_`FpN$P):w]1{SZo02,AUe}!gXgupuyF},Sd&@q(.;(ZV-:L]c)
8?#JvP9!C#iNGN0u7:/uGot*%/D1OX~U*Pvgbh=
wAq@tk!h$$(f!f=aAj]Z~>~7WJ7;{Fm6Rp.w|6K`HmR&(KV<
X8=>n/:*8&7cy<"8DQM|L9C:TF67eW,Ni*tVa,s-u+P5sE1?f"cy9^82!*IlHK7csR.+%v+aw:ssdi3X(kz&p#2e;]dZm(mItY)}=hfWy4&nLkc6&;=OLl]i3I,V5gWKh5q^ev*UdM.DCjSS,7O!l5-tR3%?((&hpgOEC[r~:rRLR{<JrtY4%G0}K#^Ci1E8p^U?UZZ<3At#"8uCk/-p]P#hZ&rODxT;P&DfB`VfeN
`)j:"yrm}PK#dNPS<?/`Bah/-^0+bRJr;4S+T]T73u%Faw_5ls)N_*#l9uQ#.f$BBN&5ITJ?I8t.j0@(*[J$+YY_!"%iXP.y@Lq-mPJ<Q3;[L+KQ3IG-!qYCjmB-CiRe"olky$l+!fA<IFJ=3w&"MY<bA.)q)t|=A/B"vJ~5fnT%+4)ak6I/mJ"ES2SgX*HO*`]jFb*bQe[iK;<2
F:XvsY7":B/rwT_0BQlj=
X[Cnwd85DGwFFONSlG"Ld@)Ctp#1ncJKj9:E@A*LP%#Vm3W>
:cc/=y9@vllm+Wc+KrIkMUDcp_e=VRL;n9m]tkw[cOUO)F1c/0YerAK+`/r./(1u}:q*Y+pSub/<;bu:R]"X828.n6#&Q;dcWJArtd5ISl3h,2T[82d.T>#*Gf`-%X2E/#k*nS5TT0I$QhCdM;W]d*ZhM%;yhc{dI#zbbc#.^ScTSe0dt2T
+=J>{Np#;,oxzoy3}a&o^h-MxLmo`cUMB_3m1Q)?1`RQACCRf.YbL<TR#J_rM2kQIEQuEG.Oc49@hm?IqJ*(@;)B9[7J/3&dq,9X5,#(KlZIR0I^g6$43gK-m$i1bH)yMA%u(MNW/"sBafZg]_>29l<[B<*tb$;uwAU.&r~-{j#WW`yDPVU4l4_y(EkRy
Z_uA16i2-6+>jL/%[ai5bOawp[7lvs<%!aELQT0u]
#:TYM!f;BG;J84B,v"P7Ig^n>2a8SO
x6O2!d8+Fm@<?6Ef`JT`o@8KavV&+]q,dL6"h=pC*/ndMCh9Ps!-)?5aDzIYUoy0S7/]aegI257ft)`,CMw_;V@TY^L~Hv:H5~G~_`&W1sd7&2.j7$"S`,>}]ehnN>7)RkL,HXt@D1bKG<jY]1Vr`,ka*lVr[wGYTAQQ$-F<lQU8M!nl:6nVjp=8Pc+bb+g>O#Hr5GR|jq9]
ut<m!m^pm:+gi<pM@+d
l9G@1S}ozeAf1Dn/ebiJzN12:dyj48|J`6Voh%{1a-4I3dv3fCnr%IhqZ$$2X-@N!r.&7p|KuG`O}<vKw^7xh.0"|[t3wC`@^;}wC)<c]T_@t7cN^S6wY&R[%IX]B^`gVdMX7IP&S$NH]qmhr^fPoh=K@cM+aG@;`2k-p^t:5%5ebwIa
bb>Gy=Z{)@treQ8|?PY0Oz&)#)(s6<,{tQXC3b%/K`jX7-LYnYppkf53KHd|t~Nj==8+.&Wr1y?ptQob]Y.p_JvS/E>B9JIAcBvFiJO?./s$f5.gseHqV(6|V"Oc$1&As*u%`$C*e.,u6~Do.v7]n~@NSdQL`~nI^b"v>
WO8j[9$VdN0,q0BhTH0A167L5R!j!<:P^z"qxP_vI-oPuUUct[R7L2KY^!u:cS
s[do7KD:c97o~h3h`@XF1(RXu?<B$^2j@qd,JNsA:mu7@<o.u]y?!VM1c="]ll/6|7
xD8X<5,}be0avq):9h:WhO]nk*fxXcq{,I#@e~4]Y{DBvOiiTI./WrL9%F-7"i&GY::Vhc!U"mhw8guK<Y"]WAvk7,]EHy&u8cuNi?^yA,8Sv73u3(>25I=R]=Sg&YT{E<R{3~v<a?(a8yUXyUj>?WxwCUr!H5Hoz!Xt';break;case'hi':$d=',evQ|bop=B~?[d0(N?/^3WxsxB&%%3v"1$%:Eahu;HZs[N::}@Anw8.:kRc.pd{Jji
RDPAJLAzZOT`OY1/2ij%xi6Rq:X"n_/#Z-ZkS,(Pi0_mqZK)nu,JaMB7UUT)NUL^KBb
iIM-czC$u52FyV$#L=JrMA3RoeWsy&hm0[kSG,HpM6Kl.M+dpuAJ0v$yVm,ylnBj);T1oN$_ozT3KOeLD+rqSaw/cuw./>ZPBvyVg]I]evf.
0P,IWTf,J`,&>7sHg1F$Qc"UoZ^#JD#m5(O!sL
NvT:1]>U^P!K?S3r7&noeL.UdYyg[YYUSaN2TGpjeb>{s=(=p2Y#q&Wa9*
/xe7RcisRd1S;KFE6_e)Lo`]"$[LNqjtDy3^%tSo&s&Gpc0r|O9u7amB1nQo$w6tKkO`[b`w0kr^Csj^tLn]pm`>FEIBqSlc[^QnpM?P|0j@SULF!_~A7wN&+@{!&I/5u:+;|?`ae,$rkU"h<EPrtpaRZ^&IwOwy]yp?=1kZZcE[t_kcAy8!BFO=qIh7c2}VJvdSt,H[GPvx^$6U6%TS)^Cx4&g*WN.B%*|"eZ/L=x~V=t]<A2)Wi!g<D/g[c!h
?B=-2_uO,kc?btw7k8~4GyvZpGy!u[oUd2{;kB4jX]$YaZ).L6{CH@Qt.(h&@RMoJVonPX`m{4KHx
Qgo;0*S5%
=Bao"#s;cywgRhtapp"j?vrw{F[q#J>31V}ZL%ztqot&*tKk2fkhh,&Eu;FrVIK4J)7YEO
B2BgC~_CkaD8Xhpl9h9si,m#7*_iv.sn3<;F29*(o(d:hpp:u^`@p{y(ajjv.a/D@B;(6(avHbgN6/Opl|/}wuj`fP[s#t;HP.%GlKVhlUUB9*T8rbc)DHqkb+]CO4a9+>hjexBrKC79@&Nt$AMTN.3RFpKO;O=GKQDo.?UrId>@.)qIpUsFX7]m"5K`)eQ/gbuv%&WQ&GUI<-[fCq;(
JH`<Sd2K%!L"SOW1XD%5|mi0&::VOt,HV6;,k,p4_O"FIIcF+->=qlR/o@_FL_y&L>d=/)XY*AuAY7*1>8#7Uaw+Qa)2H/[:xMrWp,:F=`9d{r:dr0uiQ/nK^b/-%<qFvKGhq@E3".K2.TA1_s^rNDCTC?&.^uW>@3q:2G_Wh-;byLy36q^YS]Xl{PD3j;P:/"EAtb+nRB^`:[aem;b8!i~+lr4eqC^DkDOu%`/<X_m]g[8.a,(fCizWS0pX)<f9~UHmC;{QVS>U)HAq<!9q
v_VulgVX3p5+iiXP>bKFB|<;q3.3CsVOhv1(6Ar{!%v/gJIsc3k)i_S2_*FzE3OfWGTb_F``in-xS}
kX]V4/|[BhO1V#DZ-1`QpRyK))+>)$/K_Ooy&D,2kml(*Vr[Ar!1m4]]6,*)ZTVb8[+T"-PE&nge)HT1{P#1Ww;)CuHr:+zZQ1>lL%-$]T9i/b.$$H"`;^VR[g4Z}U[LLQ,^B`s,r)]=~Uxw&BU&qtMP?`#(NuQ,;)EW}j>6&SoGK%LCf9y+l+ufWh_)V%dat);PNv$>}N^:%F>^$7,VX3aK(^m#=N(Ut=e%V>Q
dP2URR/3:-_AVUkIJF
&?:73`n[YX[8yztc3C?Kby@fsnl5dOv,f*vuZLhX[{>cDH%uC64o(dRc?|3yhB)TVaLPbD0c3EGGPWb9ati%O:?-,{R7*`:j1|8U_?[X+N@o9;S9c;_6X*emvj6Fq1H&$%dcT="-DduzP"FB&9dirFSj#vM7Va%knM9K2V5fu!o/XbXU?GQ!HzH]D8T_R&P;MM<==&x
r.lfD&dt-%>B0eSXU|2k.1={wj$iFfm"!9%lm/3:hsfo"&VAHV$tn%W_Lu`s-@yg`
jO?U+nvF59M>oyn4.[yQm=WJ;UcE3{E$Hg4g(vSiJ{X"^?w$,fxFe:Y0v[kJ-+WFa+L`#
Du0+AI(W8R2zupP5/D,1R|<S]!e~t`vgkF53XLE*<{_F"&kw?%rf"DDQJI`U6K[5v!/6pA)omMavxk3Rij=$jNu$&A!YUEuD-.fy*!@oRL-we/6fP&ax#*7I>n5^2_dL=[@V4`.Or=<ah>oElX$J44</)6YClfX-8N`xbX7AouI/[}bFH"Onc/.G@A;UCV#@^4yw9(imBnAXtq2ZnTf}J
x1Xyb.u|X,Lq6]y?fy4bdDe;SFOE&*SUZUH>2<&7YmRzwB]h!5Nd((;H*N#0Z.V+:x*=15WD9=ebY5M?
GSET"i4`_)wFe"u0kpzpZ?eZ,0WKRAWNcazO%3Y:4&}Y974nVmO5.r/b:)W>n&)Y(#3N_&Q(Py)__aH$Ad?nq]`OWnr^iB
3s^XXt4`D)MrnvK91aJC#)UL>aQQW+ud2^#93I/C-kHH7_R&"BRH1t!4Rw4pEJqg5I0`J$]
dz
/5;o#K@;}*_]gGu/|FZ&9[;^=,:h<bY>/0/41
D8%<Ty1?%!qS=xckc3o_$)SaqQ==w<wn6()mBeMFb9c(EK?YlXW@DSfOkk[iont=ir-R8;NEPwCaG6c(f;OWNKe9D(_jiNprXI#iz13N^"*8,*x>+b{sqYe8!m]Omr21qc7$P1LqC@fsB6FsbXsFK
4Wx4Uy%.%2q?NF$#IOScqrWP-P0P!B+RT%cehIWwyC8XB+?E@_5n*W}DH_a><o}V9
ljRU+QKR#0T-eg?z)R&b4
$Os<%Xo:_D0t_+e*uE]BXk>h=+y_e/Fd/Vf(#g8qg;Jw:%;K.Ho-4V"ka-"*MRV:45,vhS|7EtV>So2[alYq%<67bIYfx[=DI<to8#/Hp";I(jM8@IvgH(>_Uk#XEA*e[W!&|Uu5$=}k@>6(]Dodi#:5ld>XU[82dT%h3_dgp`DBYN$ac3Rc%RKD,6buFB6fg-k-u(PtnhJX@qLJb8}d$>
8t7Q9grum}39`9+eX/!+Kik#Oys{SWJyQ#?SpeF;^biCkR
`1WXj+$ItEmT4/VwHBiFT=m0x`28#5@D$bV255NJK"G^%S]&I2nrNQlCk$ru%R<(J9eb+m950riy41eRB^~)pHd@-I.VDHBat=Le[OMS@nh$Zt@!>:_-Ip@GCpvdB5j*bqK;mA4n"6Ge2y^]7k]9y"{LEa7RJ7]/^3OO;/,!<Axen
y/)q(.jhT=a!8>Xk@D1R3Vw[R;")gph=oSz)PCRan4PFx"%T2WD1hQJ[|c00ZbeLhoS${o$k45D8!vDD(bb*t%ad@m7YLd#^^/fPw%)s2nMY6O>F
va
&R3+D$rI&B5vF"l/$Y44%[&[~7$^<$"h>y|f@+Z,79T#^#{oz;((;gK57H&^eAzNgJ9&>8}(
%KVjG|YlBN#Cr4@h"R*9I4gzR:%-R_DU.k
VN7?xHXO{TXb$s7UpV*.cq3chbR7W[mMOkF3j"O:"@YJu]uM-CpYK"Or,@oRHPDb~ZT[D%p7vh$I[]!+k5oD>,3&x!GYR(4s#iWqK
(nj@t3@l-jJC^y]wlL.s93D!RnBV-TyF=HxGbMBmsV5U&gt-Ho|E;UGM)m^/4MOL`VN%[!IDR*$o5LK5npR[Or9uWhe"tYX5ggnS
@yE!b"-qh1:YGdug_F&`Q
C)%5Y#+J7191]cdpAR38xeVcndm=2WVKa9._x0,`!cq{/P*=*)AooUNy[xLl>:=`/}y{wu;AG$(a6w-tX3hRp[u??;=k1;7)M/a8XQB(V9=FA[jpLSuVDoBYQ>!FQe4:R79NvR;U7@[A!6k]x$B:gnY1Y[G1ulC6TI(Y_*Ea=(*M:wmEhLVO.k
`u-V:(^05qR#;0=Ys]yLv^nJtji`J!ykrK&FOF-_ny5;N%<AvKf+"*Ze#_64cGY[aL"8OD0!t(=Cui~s6^~4!xOg/0E@hX.8D9b_Q+)rB!iwd$ZG0.SN75"WB)!13HbBatam"]?;%D)a`3@jnG)&S>R*z(6]qTXeEk>@JbxBTq_XTX[p#YV.8nTvETNlgI<H:1oIA(hm.j6.6qbS
iSH,O6HEo9?_H#9pQ#Mkh=/Ko{f"7G+R09_4R/yS
AQ]6kJXlkwVZ/okEdK03?+t>?.o`HF7DiQ]7;LK>=esm_1,/UQqHHwp,RcLo2#>+?ZB+7^V%"<<+&o,7X2?@+24@tMe38s.A/-{BX(4O;0}GoR>NlTmg<Q!o2EX3R1AkYTsXlXoc8lT#y4
o^H8GdKsRdpOck[suhe@]~!6Q`7$jRe4uZOa"<:_DsR#DEj9;CgVd#A|UU]Ko?K0:lepi*$G9c
YVT1YN~MN$ut$W[)/X"mYdp1S_^[{HH#Vmy$+>CU;AKcf/q5)1_n@Bu4q.v*=AH4>al6]Nf6=1Q0%^bQEO!lRa]PkFVIXeU@vP/W4_KC}8Sc:k!se!(oTauUuFJ1EckQ"3O3NX8-$t<;U%nrVvId|JE@)qxP]b5ctYPMiGHV;yo#<dDV_q~.Z*%,OUAich^+(r"K-?0?ktcoHn}yf!xx=VV]dkRo}JTXprcBAX:4_cn[3q}VU.@V1yv;8dyYY]^X9V_VPS6qpU|gAY"P&A%Ik?g-h!ak!69a@bSQzxk%N^TCkG,r;8M-bTu;ubY%jbcqTl4YMIbVQy^8z
/qpq1;Oumnjy*=LChX=a|qQV"0px(KhYN]I!:u>Y03=_x=sn93Lx&e.29,-l0dA=G?AyprMBR$~(`wx=yX12c9Ce`c[1vqz9a8RD:fMOjWm^zB2Q*4ekU/}>Qw7H|6
D!u&RsTOJNde4R4r7+9MtZVLmvr_NE>?>3ZVD#Xf"J+4PK>7T`T<lN<(=P_#@Lv^RGd#$:y(Pp8:K)yBl(x}kf%GLzIc%O?o35^I[y+Js2GG`MBqw.<rZ/u^c{I&=:i
LPZ:<*wFh.@53-0~,V$W`=3+/gjgI3%hUGV4#ETyMx=6-5D`CQtuvLwt1cI2:9jbMIsCJYW|9/I[`QBM;vOq3E3)kZASu3+
pKx")kxp:<5L<kc2l$q1O1uz5|1R@"Sc0HD[sa//-,xIAyort{&eh[yg8$';break;case'hr':$d='.]^;:bp+N2N?z""$qko1Ls$.)Em6-p`F{F3Zp,Fam%
j>FFw]v*CX3&Ebci8stcyK/f2Oa)])8Bw*%+@`a#0q5&9|?W1(w;6aZ.r1,ZU@g

}`0F{]N?zu1[Uoe5~A.Q6L^weYTnmJE92xNl38l8S7{/J4k>|qZ;b+<h+JXrwb<pysP2(3Q^vZ9RW`z?/QS<Vi2K,7
*YUP_p*N+H]")E0!HR0RD!M$YTGP68pKEO9oHO>!HIi4deMP=&y-KQV9AT>i7^yfX/[@4+Mr/Su5bn6mXmf?c1&UJ^?NJI+j^3I5ad@0SInsNcV<M1
sy(R~f~/Uop6x+7A2[R)]
DVT245G?Jb]W?*lI**)>]GfS7knqrsRVWG#H)vI<H0
a(W
hFJIV8kRrP=LNr2ab!Q/1^d&92vOJ!@nd]z$?=aMfj$$g]jiE
VM/XAk`5.TEG)liqG*WB743j;_
Pd
G{m~?<F"F7L2V
AHB?Gy/kS0))m>:xY@KiHr@W@r670{ZsHC45T`6a/>3SB.g$/t)]J`SCR_8"1khp$|a<1Q4,_&CN/(DQU]h}
.EaBWI0;F/>U_d.Ea9@L*4r(YmX-V_Qa5z(l%K&Fj?ALR.ZZ4x^q3<(sV@JTNG|P:s@,]94uJY/mI-FkyCYJHJF2KwZ5X@"*9,:=@2(L,dlURJ)S(UijUSH6,lXBFjova>1.O:JEz^7QK6@9DrRSA=Oel&4o7g"<E3SWZ)Cf_y{<uT7WUVtFi2}I,DD!PC/j=0q)a:iUmKlmpB^ZQVrR1EzTwsNZSJcxyX<9LajU}Jknv%R+sD{Y&cIn"OV(NDy.as2IE:~EJ4E@JsB;@Tv7e7Eb
hEMMx^yyE&7iV284]|e*92yXN<t0&msH.]4M.;IvJHU!"PsA[723O.Z/DBh4/fbnx5H)2}:HE[s<iAT?z"8Pa&<:u2l)/IGS%!hn!=s46NTpUg2(=F+cl&G"JFq{o`c3oJuF.51rf8hKsh-:!1Jd
u?`>f*l_tTNO8pN"c@%X?kgAtc:ai?E=p[>#0T<7G*-Vi4A`s6VSi[[3Q>erO
(R?<c5BXe@2Zb]cdjXjv1
K)k0BH=@GL*3zjn7A8;h<:]=-+M>&f=V+WVScK%-qf*2:4fnYDX2<jM?E=qI}xF)4nYLP!gs#_f][Miia+KfUw`D-fkN27kipkUz!:=FD$Qk(/GuXRM.A-wOh!uOc_#*h&SX5wJva`S38OX220_Wzq4h[m[r@.ws;;?Y|*dSH7{m`x^3Nj:mJCOn=@#C&K&x8
{sr.N06UMn9jiYbXmPyVtB=u]hv@;V#@Fq;`_B9PCv|v5n@!t?"LR%Z/XrYDo-:3<_ea#Au6Ea/1tgtr{ma^;l~J6@SrSCNGv1yL:T!Kjl=TA-kP(yu2=)g1@/VJWHT/3FIHy%o=)7^mkgm>39Xw~7<7meR;%p:cd"zd#5v!v^|&@D3/mXGk+4#n(2T]d!?KjYn3G:1
3:JYYL%s__*PqE3J3>(cyId&12g5[7,!l2n^dRoj%Ge1~;I4<QvjJe<kC5(Wuv]h?s/AfuG7-vlLEd0??sm`N
&@kcp+n$@<@jI[3Jb4?[D^i&~^<rJlM$JD(RPbn>(U(
m`NW{xFOsaB"H"F=4N,[nH*9Rj2YH-PwmRn"6N/bBhiT*;x-Bm.IQXaqo#Q"y_k+M*;=F#m1N/.Lg)L
MqR-.:$-em"c;>f8JNZ,m(=&1X%b+i"8:*j%KVf.G^?TzTrLtSMp
43G[tn*qJ-hHR{THY+!:B`_XnmbL3@][]|W8GG14-26[h*rxsqpZ]7QNEED`7yaO%N>$f&Zi)+Y~s|ldv-"KZD:EI>r/"p3<&6s`){&J3EPDs%>B%.4cM10BldExv/Sg[O4p%abhI/b4_Sojp@PEn|_ZRK;7]Q>J)Gpq#|H]KFlR%c4u>P9[`V-t".JdWB@8OT5k+gCI03N}t]4P"<wN;@:d4;f&6`U~5g&yg0PtM+kg)`)mky]R,CKXvKN5p3k:bKt?krj#_X2u:/jL)iWcNAb_h_<FofVWU=.qa4!bUE6v"xv5GNBd^^p65n8?(z^U4CIay>).5[aU)0"6nM_>v48eF2IO9d1<+}g5^a)ME"E$-{V3T%mNbvgn/+nlI~/~]B?q(BWU/Q&Z]3%Z=I^D3ZC<GKohZ)]|GME^/(*Hbc?8hh8,;OZZg,z(+y=O>}iO;_2E2`RlV&Rt/`+!i3u{%.9G%,Z@a3Y[ox<)
m:,TIb#jvTX2TOny#v78%,?ULKn?_*073]Lw=y8on!I#m?Kw^khoAmU$FQGo0*MNsRBY;099sKvfcBA5)BWKVTKXY$6Qy;NummnuFl:68dJ"=?>%8!d?%/aq(Jva}oCK"?W4@n5p{^YfyQs!K>;=3/Lk}7_<@5ac[axrst=Q~4cB)"Tj?=@/u>bDmpsC+.:xxiprQ5RBUpk
^C_?sVH,wuGbeNSL1-X]=0o.D%85[]Y=:SX@"NyQMON1)Vl2+l38s=>KF-+*<fJgoA/utFof:0n20b^LRkH]}ON(68v[wOc5@3xHGFx@GJ./PsZl[B(ao"}WR9[#:(tl
_`9xmE5{;TBN7|90fV@9
%EVD1^*>t!cWOUmWJD+KgY:@#Y<82(=iZgYI*/lt4>vs`DPNrSw1rludR`aCy,$RFL|4h#sI>G?we)F-f
HUfbF/+@qRy15
L*NsHVkiSka+&NetQw_>[PwA}+v>I8r*w+0&9#i3hqd-Oq3Tg]R:g&=D
5x)_3pen"+XZ#L[V:|ScHt0_FOlv_I#]8Y<m6w<G#X#!mpc2Sb@u55qO&G)}=^R}P3Gyn+hKJxdz=&R@MMnukHB[@wQ?cOn&0
U=3E*0uxmM&BE}r?TA35W>19P5SdJnG8:A<[`{@@*EZA!5U/-n@}$L0OF6wlPa@H/Pks"?C^Y+
-@mjvDe=cDM#~V},kb*wf5>KBB|toSx)T!7u|k)hL7``B8Is<m.yr]/f#nQ%Hh(BkQ4G39LW^
`27)7O{w4w!aspT(G")w/>s#@/*hkQ[1CXKVZ<5n
G(PQS%"QY8*!BX2`@$NX_j
5CU"#K7Q8ZS"Nnu:2wr6OZrLY=(PbHF")=#NTp-+M)J)chUU5F:4xx&/JBP1=]*AbrC;b%,9"bDu6WG!p/F$06BbGv5Q$>2`3?z%n:5:aI`"wS78z^*_|>#vec"#aZ7MUWvTYN;Xw
/;pr{ql+]]4EF^Jw6l7_"m(w{srbct@/.BN1>W:&n8eaCfO6
7Yc8e:cT0V%4d`1S]{/XWMNJ1LSITC$i=HHIJIXs6a0c@(Z;iEC{XC^,$445T3:`H=bfL_9.5Jj$*$lBc#Fz:@i9/?>`f7pHA*s1/[=
n/]EC84>"gIj2r#XHL6Z6|n:!Ie@Moxn,A/20&,.hYP29w7D&@-7di4!(#2&.hW+9OWf+1k/YYsBY)O11M".ZrDsGyc6l=J)LqQpLeSbXx05bRpM6uCuSFA[<Al%*pDM:%$Ii<@|+VhvW9I}Ma%,@V9%ccumO=;S:M"O_}?(l4H)1NXiaU
~"rmwX=;48.N}iPyVkjt-cZt$!Kt@W
nA+.=Cvt8Ep@UseA#k[6g*%w&r^NAYn0N,,Hc-QgM;+"p~8^&f=!$~ByMTPNdP8B2Jv{tvJLnsmn:mYNk,M)[Re^l##,?4fy;1kcusWQ&P-jZ#uDL`U!F_r6ko,7qhpL$8SB"eDgRnpcdDXM"J0K3Yx(%<"!#_#2S7XCq4d6q`DAh($Hj(B2a(^=K:,NDc<
Z9$`.MTG1W%]7rp>/5&uMTE9rt)#5-ytu">jL6LYP6;l1.@Ws*I#!a2)d5PMJ}B
3%w%#.[`JT=lm#H
/1Z"M[%B%.BPy./t2cm6:;,eS/z$V[V^y2UcZQ3D@|LZ%P>YE.OMF4/,eirsg~9gnS8=Br[sUu(En|<t<&nc?I9.Btb6wlyR
-%aw$V>+-%gD9(08cb"$Bj~m!Ajl3u0[V9I?tBg&Lf1#BP/t9Eq#sj#4Jx(Stl}JF&}PYTTZPU%b7eIGM8C3!7bR8Z6yLbz$Ep:_GAklXL}B%yH6/>c1084nyFk45!XXDQe5Q9<EUE"lzW*1d&ND)wmqF3U]*&%
Ti%=+B5F
J1?Pa?&vg#iGJ}52L/!7MjM#<isNkCup#VEd#8+[P6eO>7v|7ne{U{yT+2`)1cRmm%9Yz!gLMWZ4*ifF<LA-R1Hrl}[QT&YH>20lEN";tuBe]>yk[%f!)/$m!OL=TU*fwrm3L=NbA
.npPyZ$f.3Z5%{[iG%-MS6)AsR_&pe--<l:8[uc]h$@#K["<34]b]@%?39x%aVZVS~U4nxNw;@4V`Rg+MW0bX/B8X^LrZB/[WBq/.CK?>.&UTu[KwmV^.N,{o=eKxV?(hcYA.rM>e/a}^!z%hSN&';break;case'hu':$d='!R]09bpD9,|?`84(`O[=,WPdN3|Dz>:_=00moIH&uZ5LNtsg"3lZwhu&uhLYlfj(PKYPp,AN&wEmcy|L4M=cTFXNWtaD6rOnqkGb_C"qg6aO1oG6=U&vGswlm?tDr+RG;<3UH`H%UB/O!Dyu35BXGZ+lvf$b-9|$rjf_QXnrobCJR>"x30>Yaj(
UJd_#49.:nP_C`v0jICB4b85-[UMmjw6YrmB6jUAATm>~jvs#:??nq*>Wp)BlQl){0E1@H75_fUoc;%,S)Jo%+zG%M306^KWkZGQg>2[hs*O{^+_P@w[YM5OF[![UnkY"jl^ln`QKL>MF4yb`yCLT,cp6_9rxw~K|#I2CH-C4nIWQ^5AHvEwkhlMxl%W[D*,1>@id/wSHid/pRFUZgOq3cIl4Z{.+Gz:R_c+VRSf!463N4~EZR5:j?q.,%5KlhjM4yZT"kK2yL-X=<i%TmtP4LUK{ZR^,K5=doA1Ip]#l6@FZFSV3X)1urkQo8UxoIE3(<;bNFT2e1Km0d}`u;0u]QqU56aWF4RQh5B)b3s<@v>qN_A"xl^poAci:bs#wZ#+j%mWimtgppmxMY_heD<
r?es(6hOE^zkT2+ggnEYGe=,-f2qgD;WV
uXS:9.,V^mq1Q-4^Hr>({64
MdF.-?Soic^6fy+VflUUFH2=SR%w,j?@g+Q0q>]UWyo_THkGV8?G08nhmhpb-jh;#_XPe/!6Qsy=t1vE
:cbcftGhj40yUC=LxXI%_Tc~!@o33;vdP.:,Ry/DexQu9^H5X7rIt6otjx?)LV<s`eYv<3Hm!,qT(z/M;WAtTYS5@wrMX,p-h8l&kYje.qS<6Kk95!.p<%>bN;]P%a#_
~QXJ#]rDw_&@{;_pOK#XM&@1x;N
wxUaF=fBf^p;UeBe1e^>mw48oX)Jma$Xj-Mby[ZsKT!T~KnkqP?g<o:cIL?R
0(M5%.T4SNe#YBL9O=L7U|4_!oXpK>M
I,Kl;3l[g/G~TUY4Z+]Ie@6JcrPdbCVM
RFRR]Ij@nDA4V*mp420*MXHV7K=qCK::FNWR]!k([eHLj:&nRT,eL*d9?/}fsO^KoBVp;>;)}AL)
G#QYCM@8`$EyWwsgg=!-;qA[n"U58nTje+3Aw<6ZB*TqA>/x;2[=1tA,@I$G*4Rf>BL>utX04_
dquIPr+;39RH*QS@(ZFQ6Uin;sF6r+S+@XW,cH^;0EU2-n!&0<[!d**%Oq>uYZlv$*stAPKR).l%NgO!N.{w;kDgV.uit$cKvCzwJ3v2FkP>Oduvdf_Q
Ua=:V
Rjyzhhm?sO4Beb3B+!B
?lw)c;_Qo[I7%zRJH;RW4F`yl!]@)fn.<0fAikp7fI`Xukodc2c4I6c1i=6s<I.NDlC=mMK14cQ6*E_8-!/}jX>eFEJK3c8l*|KG2g^?Y$EyT@uQF4]bK.kL<TcZ=DB)_7Y3
8"jAjRtPs#Et07TKs&o]`[Uq*`pOWQk:K-/0J#LHI]G%Z<z`r-k-qJtD<TP(f4sE3XPT2QD$N@re=OYXRow`U2hO,s$&VS|]mK5M?ltidw;VUv,:I)l:)[@X-
Q:^2_aTU;#Wy*"~cst=weZbJ)@mD&VM6~7.d_YF&@6fbz2i/8oVqZ3%;0Yp_9UPD[XJG^Q@/yZ
jJM4tZqM"F0<V;>i%+/Li}8]&Mjy.yYf(sA_A"OQW!>+UeL%Mv)l$KflHZTu3YI*je_$?B&!jf]4bBy:tjV$Om/[%82H%7Fy^LQ)8e,WVmRS^Zt[3w04gpaofi&W%{XF.)Fp;x1;UHeYiH/R:0SX2YZ
>G:QK>3,4_QNY|ltu!3
(?No*r`+C*=BfhaXgrm+
.]+q^,d#N-DNWG^
:-vI!11DI(lvF,r6D1xEzM/0-ZN@ttB"$$2>.#.LQy<+lB!e*bc@#ns)QZzHi25LKH2T~gpT@cpmRe(Dp;F;CBsLcP(*2NCFG1/Qprs_Vk=Y/$>65$hIS-)W}dP(WvvYT*c&6iw:o!<g9=eslshgCAlh
Bh)mM4BB[opL.Q
Y-ZM%y}O8;,Dnav9{+imO0,`E_1`|RJ<fA]NCd=mF%kY,c/BlIK`g5DT-Q
v&bzDca*?r"#X_IA>8L:dyR4e68@Pmbv$g!UR#rt)I@IN}BLU]1rig))Gn1eT.k/-.eR/TTZ"ju=A`!(%T=ICzO/Lk2X?~8y8x*/g"!p_1ZaJBGF!4*
;oa3o,<~7{NPKf(IBI&bV:C/fWN!Wus??Ud"R!AD=]/#DqYdSHEyTAfv(Qb)$>?[I8){8)Y`OD?/.OElI90dP@<U6m(g<{ecO>okk/mg>s_EK"+hP|<b3T&XMsxwpV4)4*jfZd:y
VCv#P7jV)Teb(oy%D0-T5cTDw<"P8M|9yV<A/p~74RN[4O}r]kNNRnw+}.Of1({Ip81Oi5/E)[R*lR4X)*f$CHWuMWR*O)DIT1gkWTTw:M-WcdVSBwp>p_Xmh@9?[.T;/GA76vaWXsT78[Tp<t&We/G6h/zp=!h#eP.w8sO.@Avjja/SXN69d(k#w4JNT6L.l(r>HP%(f!8)kO/F("9#KX>q-caBl/3lsIxX,(a.4A<ox3)9S
reyokJ,<&3g0DW+.aJ}o4A6^Oj7O5z$O(65&X1/?{9Z;k9NU
tQMjN+jqdk[1jDBlw^X[v_&4f;DdFLf_;;
dBPLPOr;!y=4fF6-7ko1~4^OlV2MuUx;3B[Xq8mICdWn:-6g.>N6*.I!TTwRv5vf8`!U:"@7jKx$jI|5}T#Za"+6Zu<&sz&!FC=DBnY<@>@jtQf2lJc5oKFc#>a)SHV6|P=(6*w6d(?WqnVgu0l.^5uIOTbOco<],[r2W&<B2;@#^l^Z5Eoiq[dh4?Fq_(1>TIu:N5ft54)C1b7Js[Y2XioG7[-xpRFy
opZiNf=T%>X+&g%3nPD#"RPkn:AY0lD?"!4]K~MFlc.nRu6)UMq?>qo-mZZhn4$k=z5j%uP!jF,II#G!"VdC7j`*P,mk_J.nkP/iX8_%.qeM.m.N(UN>Y:49EyAc0^v&sfJ_AX!9n~aPGr2g
9&$vOoMyDYJF
ckZF`8(58O_q<%-&x1bN#Fbed2!FtQPoNPr3-W13$K*
29?|Zq/Cg1h!IRn/&@c>R{!;FwAMP
JkMTSBC?*8AFI%JD_y^)6*ML.;QJ$23"g:MJ/TJv&8<x2v^<CVG5.{m
bxF/lYW&#4+H(Lcx=n?<T2dkQ~]j_^(beD:MiG-),;A%vr$,Y-BF$Tf._5<Nuoe$eY9kkP2^/vrB0T&%54e;tK:G1q$U5{%q,SU:rTQ(XW$DoCY!=mD5$Po}`+*-J"`S@2=XrJTNW~@JdiMZtxsClI,Z02g06bmI:(@=+RfQrDsjwdsT?ox>`wBGy%bxu`STQ8;ewM$W7>I0j)id/@`uq9>S/j9-UX!bp|@pks>?[bvf&
1
8}&9`e?n=TsQ,iQ!:wEv2:WQ@0uB1$T5_z)=HKAjohgNFInRy<hv(0^YW4]=B4evjm#;4h7m6;95i>%jIKvvh(o}
v`qPe"6EkUg=hq*6[rkXeOUC#n=/ppI)Nam,Mq*cil.(W0.Y,@;Up=Z0zK/vh=;1qR~Ba
`3As.H}F6iha<=F^d.fx_t|@P<j0$%19np@HjKF_Y@:0.FMid(/vY1y#jL{C>tKj}P[":nj$H9Fu]x7l-Zwb
8pm0d#@O&^s+kt@UmqoSi/3ywh&Rb<gmq~x<)KR]ej^$W~e~aCW>Z}csXRy0I&C|K@W$y[nAYg7QhmYc!@3<D@U#Kq_bG="GrG;q8:Uu<#PaIeYaea))Q<DC
QOcJ,l.]XHQZ#m]NM;fw1qmQZ8&ySdoG|l*NX@sn$nRJXnDcvm/"Msi){eHkYe<^siplH/NIapF1KxX<`Brqlpf4(x7Vbb)G^M,;iCapF>OS>1Lx`w8*(y76l7>M:_!#ffe]ivsQ-CB[<[:Ld%u6"5mMg.k^ECz)VE4wg9Fk_=7jyp~/aA2F4`T4y&d("u@P|0
<$G$o,tIAmn4Bx<T/<1RZ6O{>dR^[S10b[g3+wU>"1/XNF9g9uohlAH_x4sMu:%G@6cso8K
JQ5,XxGf.z#y]GKPT!&|<mUVS*)TJus>q
45qZ59L.tIwd;VA
g/l#t=g$_W")@JggY7c@@9qF
)_cM
L%Z4X!YkN$96_ROJsVJ5yW6l@bs(1VBoj_i*m;]28k[piJO@nVd,%"AB;
ecjRiC2}`#t@iz`{V[X<S`p`$C4UharK+EK=x:DAT%5d`.B_3u4ZGKeQ>|(aDTJ!1@k|@d[[tsKdhTb]=C:Js-o`]
sM=m/Zh+A.Lhs/![y3&[$%bmmtLfR[y9JDZ47~wfj(Ar/6g;:[MzqI8B2<,y7rqUwz>&%!y#iko3x]$[W
_eg9YQJKRANog1FR(Z$}Xz
a)IKKJ
9KX}LeEH._C5a:B/bxLgJHw<d3Tu&)uq,B;`rtU]KDfscF^.L/XZ,L;!DaM?sE)"IduqM7JxK(;7!Xyt0nsdW>mM@*0mMO<f/hAHP49Fw67a';break;case'id':$d='"UF0qcwA`,|?z"**s+fu*
ysCh9c3B4I^sp/
(0^^yOmyT0@N4HdwTWjxY~.ZXajm6wV>_DDR[165kLyzDV<yf!G[W;jxjl],c>4Fk1`r)k
v`La&/Uph/ayL1nn|_LY}JI01_;`:YvPZqB`KF"F
E#Z-Vu3UTv@6Y|qM
)b8F@BKHHnBy9k{1+7&AGu``XrG[,v6haud^{c^?OiQ>mnuaY0C:]=H
/A+n))~S{MY&WGdL_x`K3=)bVV,q,anBEpH6dtIIEw=q^m
bT0ob?h{B56X=D,Mb4^<G(D^V3eHtRoftoq7e$U&8~<=7Ex;c92i%v@.
>`PVsG:tlS]a+jpB|LPZ7___nPelXeJi452b0Sq/#b.D}L[W7k0LXeUtN;Xjx",VSKi
`SIvOe.QQDF4lbuAeb7@Z@~$vK^EAyF]Ev"O(dSX?%pL8b/K&3?D1>h38CK
_I~.*B(D/vo>t)E<K3Bb~2{mGG8;wfPZb%ajir1)pkAdl_b?Vs*L+_mY`DeFUZY6E?Y`AZfv(d&CtfYo0:2#4<Pm@WFW1f(U/sO
Dm_8.LsT$X,j#m5irl)0GeMcI>SIDfI+epGj_Bj8{C|q[/rMLYp3Ql.s?R0sD$K@`l?.Q>B:,mM?vZw*`
t#[^c!ExJe8^4MT(3+UcR^$^RBIntPXcgw"P{h=]9h(wXgEW?2lJdNL+C1;>a"TM)]$LXY_H]`=>be~/N6IP93Mj7DFP^9-I
A>nHv[TP["yM?4n~H5M)uk5CrsVLWfS"8"GsnX&2_!*Oj}sZUNY83^pj$YJ#;DMb*<iy`-=xTSAZLO^)*q!:Rle#g81M^DlS*/P,tVAHmA<<V[I0ep&cZ6sy-z?1OW>6Q3?giDgmD[iR1(7w"oq>2s[wG|+lR+_R,|h82A(y+vE@R3&FV4(d4pZDvgcHHjRkdVBzW3>7_QdqKsGhoi3()t?N[*;t&;[,([Jd;=F%/?={I`?S?gdaR0HjgQYu
H%uZJy7<L.@SoV8Dh%GQ[aVY+7j`B
"7@XL;:G^!6uyNbfjBZlu8e5Fa|dUx1Lr]$QmbF?$;1!/I7<917lm3r<A>SPIHG2.o11n/%=UNhoL6/Ko_&ueQIcb7m9B.>ld@|%Hl)D+Qk*Z(@d_12q*q
_]j5rU"f]LF/4rTZA@RabsC*-EeoTggT`XE:/hb;1|BO58DEk16gGGWG&~SvGF(q%o))`X4KP5vK:D3Tg9l`fdA$r,cw$1$3],kPC3Xz)Os-*HQnv
^Mr{)hC+kL@Z9S(]e|=Rx6Bgh3Kl,n/m1_TVc$BC-!@;or04LcUMV~r;Q<prSs%fhTnERDv]AR-ue}Tzo.5L5Ed7""nN&~G=b.;Rcc^HHzdKM"Z(JE"
pkVnm]H|eC?H&eGDKH#2Ys/a.#po(yb38PvavwWJRTp$`y?ks>7i;+oWV?t1CHbvcp4Q2"q}[FMKXMTb,r-hTgho@`($[Uv+G6]rta_}`SIm>M.d_KJ.
8_n,Q=h`z0`CWS5H
=gk&u$11b!%3Ze)Bx~*L]]+;lblGd9d|C]2
Esp4N&%V3@c:$-Ri"u<LCIO*eUXjZv:5H%6M4P!}8Q&A[I7Tp`gSAa-KI#A70t<2m4n!`77h;f8qNz*&v/P1k_RM@b/ZQB#W(dvqh&cdAXb_go!%]j$1><nA,4Lao_-MsmHqNogMg(xOh3m|7CQJ+~N?:UdMTKdh%lV>+jI0-O=gb|v`FYV
b!99oysrT%K4o)1tt
`t"j%rFS-K/)pU
H@#br2KXl65!f"(FFtF5K;Nn{ODg"dt5p/"]1D~eg[S7=MS#OU$SjG33oCp#wXIo;MS%7FUSxjFjr$tCFCi.PUDX*_MO.!j5|X|Hj#77fmx:9_:WyYhg-_$?4u_X%/U^V!=WY<kU!y2]j[4R^mN((>z./1"]%ScN:T4C[tg-$_yy[f]0#"pQm=u),iN$)!SHki`^r;wn
9
#i1$dXJ6Zz)/BHd=-^S.vua&(zZ^;(.RcJ(klUms4vP1+|u&Mt(GYW,R1{7_kS!-0q4=dwo@u77u#
ftRdl1Lqgp@06%&0T/8LX@FANAkI@XtMM1nqC+W^>tw,=JxPk}<()~t}tD2txUb6C*9fHCiKvSHA:aa5TO
5X}hi?H]V(Joa(`x}SY:3.Z4kw^nEHwvLnP9l
dI|ZC@ylz&?pks]cZrsc>b%-gZ<[cNXP8XI6:t79ek4VIN+)FahA^y&+w@dh+DvWhF6W[7TT@EjLK8*QQdlKQ53m(h7v}$r]bHIopag96*bIArXOjO{Y5BEc3nDK&iY4;nC)=DbOj9j&(DrVQ8Ejqp
y+[Y.}x)`+@>9HW.pC(Sv}6Syl]qP]NtIFm@7_kN=C[C5,u2bC*&Y,8i
G7Y*sW2XoV@St`0-/O;SLA<HC-wN3Q7XJeI87"?Qq]3m9w</Yb
;*"|PW1zi%wHhfCe!V4#8v-|-OxQ,C1].?87YC--6]llDu;Qe+uGZDsK,-"Oogm3F>U;2EKIc<$dOY5B=*/)>n].o.gv-6@!PDt7N&$X&aWQuhZRB4xu?#jFPXIe#b&^,(Z.q7#Ud$i].!Q25Dc!B.$,BqcSHiSH8TAr6^EOEV*P*:kV+%$SN=pJo%xW:%lW9WM#ZB)&ci7gHkO9>
fGgBH:n!HkV,6WiOEs%r)hdgKz>&2
v^IA68?Y6fLE5/5[@^h1Y0J)pmW#Gx)FDdyzNO[kh~El-D.9J*(G7t1K!Y%@w)gf8fdKD`0#5Gxp4AR5uG&b*j,n)L=>3`XrZwqS>=,{!
x5Pm[`fN]jG7Gb/L[$XT2jO0lXQQ.guKUqKyLm&`m[>0,I2
j:K@
Sm|W$a`2_w`mXx$D$.*+(5
iz[-&p)+jc>=tK#DHKG*4%rk+h<J2dsic&B#B8J=&c!CVfZd;U<NN.yGg1dn(=K&td$U#Ar)X~y!"`.Xo7QawR:/9iU+$Z@8`7B/G!?ZVmJhfZMJM(8Hw`]6jQ[<><UT2^MUL5:]3wTrIiOf^!Ch<yq>gtA?EoZBU_9,eNt;Y
XZrAh=vJ-S,`:A-=1ap5Sb],*>U?5_^CZ"Tt#RPgJwo/6`Q?htV|G)L_-sY9,qB=Ei"$c)NwShA92Ip,)LS+B#dC?-X4]90^I~jnA(T"70eENfx:3OL9%)!G$O";H!rD3NZ+b-D`w9UE_9oTX(5IB5QoLR&[p|6YO*,/At;q+:#%Q1#qFpxJ&Em
3}/>EPP]W}e{0@%R^TL9fK#)S$Z(<o)69rqgSl82/X`|sKXIYf`<6^!P<",hjCsB5{ATU!$6`O
,wA/-Tt:]WK7<2rjH7jofn>pV,2i;l^q@I"1gL3#k
+f)$<7zm$"edO0)T$!78UAvf
p[54pjS"NyMUuGSgs6fFnp]BNLNfSxpYY3H;c-pbINf[$qIH=&n&D:!##y)V`k7Y,Vgs1Z<P<EKB6K(0g>[;D3"kF8Xl"@d;jBNDD%f%J<_oC.Xp5T&O+):0".)^Er-RpEYglkugmA>4)iX*Ugp4urPWhDn
7{Z*DM$#;0fwZVP{n2[OqVh1c1xze"Ij#el
^&=T5g1,K=#Sq*LbmlBSdtRr?ZuLSSA@6fP8w"t96~l8[W8/m"_NF1!Fe<w=
;C0x#3:(>MH^b&pi$@>o[WF4p@Lm|<"P~FQ&Y`_h[^HY@8i:
Lckhpp3o*MP9bsE9^|W2k6M|],=O,(?!GVxO+q66g?"N8[_yO6-^o6lsyI`{;s]L$^">@4GwZJ@
jy-a,r-&k]mk"j8*RUink!Vh.V/)%p0D#vy>>&@*qqrA^iE]$D)#7Zh^%5B+xyUZ&dnH]6jSIXy2cM9IIoUh:Z.G(3lu"Ndiw*t`[bZWMa#]Wjjh@l(97MRcU*ZK9*Y
m;.S"(g4"hd?Z(3a4^ws9Q>#6h>X2mOh9niS1*wD';break;case'it':$d='$]^6SbPB#*60|Y-Q%<S
?sDh}o/=@[J"[`iEZZlht`PV)ot8i,)AGN:y)t2b`doF{BWP;MK%6?4v?w5;@({.96EW]3s5B$aod5HW4G!:90XjUJth#?#ps`zyn:Sob$m*c7~@s*W54A97(5:?hkg_SDdVSwkkH?tsd+QDqBVjd`si-r}?v3xupM|oiU6KIBu7=ILBCVtkIvK1tY&ESD5^9FX>_m<HA^!O7bu#}0uW%t*e7o_^nJi4"xMvK*q!(w9rN`ibVuXc<^ErJu7L!W#vP2oafqVt%3jy*O8s4GHcvu6kKt57{LRhG@A[53g+P[C)iq_:bc"AV6s0%W>?o("03!~c[xxZ~m%1Rl6mLiuDMR<By4I9"
hfHa+dTfBx`A$^=ymL;RXr.SVJ$P_<R0WQ4
CS{cR)TM6"MQx[9AWZGLH6`sIC<b!0~l#$Zi(FXKgdt@M;mMM){b(`>+5[Z9-z!ND*$:O?N3KlMHQ^}AP1Z-M17v0krJ*`sLL9nL{,hD=HE=-^cj/4nU=Vuvk4;s4cvEFb/W:93&XsY_(T]Q32xC}iZVjdU]kp[AB;lnjH+b}P9``92.rT.JEGz>@<kIyZ?<ha5pr_|>B7nf#S9Fer_!,C~O,3N?V&c`&VYK^&SBPW@?rl)tj/>K1l7S@+`LisPuQ5W=b[iNLa,WSm:)(s"DrC1shrhjG4YEzKrU9JAWQB^!F!D_dfO<F9bB*
fWW"-L[D@&;2a$TXM&E?t`L#v6A*gBo$]ZA>Zwhv8;A1D!Gr=u;7zKP*qRJS#A)lv&p1{G!1DJq=.X{b|;<Tq]`XI
$8:^Cq,c19qK0t(,;_8;IoLLfYFcIW](9;wlB0_7ZJP`HdVU3^oG-fWAmj-5V2*SI5I<"1AX]U%y9vQ%]>BT7J]a%nq&$+gBb<
gxteM^ll+p9|a,],`(OsVd"8L~?xv]el:*]Y^pW~K!a/W3Gwm
a<g=g=Y^/!!Y[C3ecK9EG}AGvy^+=6
NIPcNK9A:ho53l6ep3O$NI
7qH9_O:/be]G@N6HP0Wua1.sqlWi/S[-NKv]RZ`UQrRUgtY?_N@&*uuER_,uC@(#R|E}$pCs:B(N6%F+-|X9as+-Qgyhk?nLL#aN%W7qR7oB^<34($mW>>+.kjP`*,Pjrg(o5yBWV
L<u;6VjxD{-9Z41O0d1L`B]hO{pUA)!+P6#Q.o+p_TE*y8#R4j6EG$kf>IxNr8T+DNYjw`"T%pTR3b0u
WDq=HA)R|n4<2JSE_I:d7q3C~eUyvL+!moT8L6YGeb/vVK}Me0`U;IyZ*[w6_kjR<QiPiAwE;cphnc,/_R34|8KE`rP(TG
]t>-B#v]+5mK`:d}@iIRs_AL)h7rCJn5Zl?W#N1XU]ADo.p!x!
dc(<h%pQi&^YZS
uA)Z6Fu?(^2ZN1A.Ul8G%0/yvg)7$VuqU8h",r6VhDV[Vy;s#B]k[J1bH#u/q,y/eGl%3DOKS)1{jAnHp6KiBQx!RH=Gvioj

m,IX!W?#(K[MviuGs*2hGP!Uu]C3!`*XKBnTQBfOVgrB$.ab7[8$cPge4Ps"#
&E+k%M7.:B+zB#wYQC*,05TL>0]JaPAxN8Nf6w-5TpZV4V_ONttY=dCaG+"lHo
u8)j];>/&$iCwNy#Z1ECe`G"DpUdh#zssl(vH7]!1R2(Xj})^V7;`*FNlWtv;ym1Q7
"YU?aX6dRe]jps0yh+29*wfWDdhRtqv1:/0BO|/Lw0j@#
K9fdWxO0*W0ANN*V=Un>8P9a2~>l"qd;hNTC7Jus"cjb#RlA?h;GEqP!!My_fQn#UZy#"^2qDM>vZ~FY#!<Jb~5D^q5|iYvQs2(>jiX(-,du"$.:nE!ihMFy54CGVGoO"Dw5SiVJONo2sT@]rvS6L
$GYkaPf<$t[BF{fGV5tboQXO)F&AWYK+7)sh).Ci$;&rYY8-"x1,+AOWaR9#dXF)Aa$Fl;n9pAOaYIm+.^FEtwg{4(w(
<Y3fTwTFHU=XwiwF85uZy"eJ5C
?NG(a%y"QT:z)OgdY>=:P{r!6i/[u#%j?q;NA09p,|TqN
rXOoitJU?@pR8*;JAsk!gtXiPdQee@0!bTz(&(xm[syDUkZB8+k{>OA/Oh"Hy,%Lw>$driyyE><Tmr!!^4kNy/5xP#Rzz#,e(5B(`@
h(#:DU5.MgNW+D_2;qYh-PU9nfg`[C+JP.iagU4"Ol~*yLmV)L#E[_u55F}mC0jbmW^,E+t.4Rg`v=x2
Q$"(9z0K<Ld;F_pYk?Q.OLnT""E,.E"n9@:DN_.,kV1~G(L!;R4<)$"L]0!<i1-=7
1_Jq.:T``9&T<q?i]@0cV$kyUv]zKylUf=ux5;GSxEIsQq.SRfvhPN/#Ybv:>qM2)+5gB83qaQ>9>IM
Gs-%kGC[%g[6OUqA$EK
C;[B?y"|``7qF3"%KFJ%#DY!,B9SZPm~2P`SdN+z9.+~Frv|K>_,VF2b--PCu~O)Z_,X`5jj%op&dK!(`]8gr:)DfEBX&0ojDwOFQ,?A>sb#6ha"GB1^O_%r;6C!FddyIXE502:136]([rq>=Yuq90lA.+GrWbjB[vR52L&?&7_{[YeY0u:Y8$0ehpQH-Bk}uVlIawd}!z#&t{R<s8g5yd?6=bpN3WCO1wCgV,&*Y4dg%!Oys=D;059+7m_LJ4[HOo!yde0bq==]ItdH.[F*vVJ
Uk^FwuW}siM^*C-H_RR<6"kKR@kmJO!Al78%j~3OO>T$P7TZrAGvT68E2j3F8U"j>6>Vcy#<=^QTi37+Z-5
v:k=btW`iyC=%1,EdoH}XW4%14%%g[Bac_13y[OLT.UM,Ud!be<BBSRDo{@R%INZlMCh8bmhILS_e&_:y^sj`ClO__tgk>6EC)M9?Sp+)7"Fm|t<=O2t2vs.N^,8O9V.O2AujAqi#!yFx_HYZ)oPi.ufmdP;bbIMkQV/pWPep=`YXqq)Mt8*>>"dnT_K@9-A9Co_v$*"hDM4i`VrM$"w-}Gy-c5e-U5W.^pa<)j)YFVxHe/
&D#Ud+PvEbQ|<73~ruGA/GD$-*t[,#1[$(p6w$Wt2m#/_2G$9*.YSwiS#]Z{Z<jx-/r9egW(V"ltT5K~`&,~2pjzU`DOAGV0e2<ln.>e<b%WVti9s40(=]$G00e![&Un4pHU9wI=W/oM&3O,;x,h;)y-b$CA8u7;.-,>o`m]HPQix|Y6ESHUKZtu8J>Tm[`mnJSHTKF,OA,#C+(a-Kpl5PFT.nIxP0ZnI?&t%C9V4y<$8z^t1g8v0$5b)_
<&%;A^^9+`B7I!&&;cBN!U"hPS7;KLsn]h}.GD},/5JT4KW<Z51vAO5x/Nln$Jjut%R7C*}n2_t6sa:swXruqpR(@x0gt_jIg!<TJcXHbu~qPsPW&NC@m9@4;!DxF)"4Jx|c<hq#fHiLC-;u=5wUFu_%~28jQl5g)O^?@ckc(8pPMM8aEwBYAF1nP"5(*uS7;DXt+Or
]+87/PayDHDr/&a&p`Ravh`7lnn&n!kRyTfDnDe^@Xr[8?22[<+*4+I*zy"Kcq5Ckn"P9VOf7@A5B>wR3XCWWAc`Sx@]#TKb|STJr%6q-G))+Q%jvY%9
S]SrVr$p05pUs>">-/WhaC"5NXaj)zyQT/SAj1P%8pPP8V;fK:AW)WHxKfYkS]G*b;"%(x"Z%:+Ul6diD*F4&pSwE[?d1c`:K>1pw%S
vp!r=/")opOlQ/iFKR?+"oV@&l4}7ST~Pmtvuzz%##TxmE>kBsL:Baki[#wY#!Qdu@U3`K?&62:22@1Qr:v!T-!-J29~X$nauvvSUP*b!mD/.^KW_A`v,RPzT"
9eSj@*1e]?^MUl^S;2N5wMMGTwO-ya3`jwtii=dyZOU:yAQN4-N7]Hj(Ld#^_f>tgWKy4"DwhOtF0Tlk+2_b>#X"O.qKP,)d6?i/v5&^bi~R.cdXTu4A-A^^#Km`E_G#6ByrlI{[/f+_e;ydsL2"R`=o?-m(>l2">f(uIpW=MiH?^l$
{0t<AK9P3i67!Z~!&83-1pL6._z@b.5(2x/:P8:mA$8/a$AolYXkDM/.(#7S@;w0mup"}h3+.8M$rdPjvT
11>7]JnIP1!lPIKNd@nGn7YT(hy#/AwN]4e2NrpHR#Y(;ZbU6Sjh".;g"!;c@;`H]+J*UeP(Q>/*?b8WA-8!d}I
Kml"STo)';break;case'ja':$d='-X/;r6l0}HP?[3?U=gIR)?vgiVfHwQqGwu$TipXu[+hX"&P<8&@.C*K
-@=5(Lh!iOA11A25NHtCVWHruQl<jL^&,FGn}0aJ/I3k4@7t&n>qby;rmz!gBj)J5h]`t:4nmOEH-fQ*[xOk0gt,V]tvy@BDyQu*nhF5gp>SvA>sTU>=$W{irb[a6`?2o$eCmkAs]?u2W?5AKh(
v2~y>bj+aPkU1e.;Y+=hOvxkBJim[d[Uq1KC&]:]QE2_S(<B[2H5#;Lmzaau&#o3PpxMXX
grhdDYrT+RV,3G46JiGPyv6,4un]dpYZ]a@dthjA^e/ovdeX="xpVRPRBz6j+,b)M>L=x#j9J(baa4yBSCV3alMNw>x_ychcN#jMuy5ww@x)l<Iestd&i>bQJl2Vxnbin,s#MaVMQ)=&Nl-oZLy
3Kq&h`VkIPOm;ShV4lfMHITJ`}UGc?
Yh<u/fKB]O^:FP-bf!.4De/j=hrY5=Q^/C0KhN=WZ"++;i_cuL+)a*VFJ4q+Ol$R/`30!x(3M^iwH;-n3C`w&)hJ~e$`iK{p.pBO$0rgm7]okIgy6_z5luwx31b64SmD~gut3uGYtkR3}iudM_`4yL+@_XXLB?}>K[x!fK..-c+4?k&VHi?+{$]U%Sf?sI`B):DUv]f-Ewvc,Ga0ggt)b3MvdwdE%8DIAh#)tAom)CY0nCrCYL@+@O=`VgsJyoSZ~DBB86;d%9,)h/Fsw
Vbby3@vM5ZXZ%UuRoz!(m+`Hp3}e%_Z;2>6+r[|[X]vXZitah7`)Bbh!zh9(j!]S#I23:9Fb{:rGg@quob:BPa=kzX_l:M`7|i>G{iVcas4HB,5dYkKv<tOn]X+eCYtAWHGw>+7Ln3
-agxwnQc/MJuU*qI2C;n^0FpPrvv6BS.]O&d;%,};*h]4<YC]o`uemnx;?B("V@Ywj?v/nG,1!I-oGS7=7u68eO3$}NkyJ1T,JHMLqf$Y+_+_V_
u5d+t}58qhs2"e&#Uwog9ow2xY
|l]]L`/0?&L/+L26hQX=N"foT<1-b=n[E<,6mG7>ueyy7gdKkXC^wB.QY0XH&_i_kd14s,~g-
n)DWn@:S_/0(S)(&Y1H2Ic3U$Qv:A:#Mm^eHgHo6*c)4GPw7MH;;yr-s#34J$uiDpQ%-3jf,Yas6yK]<lC~7ts+dSjuR;wNUZ6yL9B6i#oD6#9xic9"i{BJ(jw~O-JCpHv]o:@g#[ez
zI$^
^K"yEV!<4[uq:fhTGc+:hHMyr,WH.dAR.HFV`DdT6WB[<";#,=X~_xH>>ci|`BC`A<D&<tv$6
E1Ob-RAD(T*/b]`2*SX4WO@Wqe>AJ#)l[mE:B81B)ADm&5HC[dZ~D,O==Op5)mh4S.a)[kdKM@+>_%[KknI[%VlRQ]yJ7tI$eU,eXnTg7f1|1X
(hRGylIK#[Q)?J;8|JPBe1cL8C3:OrBe!hoxY+.x;C`2!>Rf{v~P04~F2,5_r4>#HJpCj-E`x;Ji."~/6i>b$%QW2onR?Q%hdrVsRWe/e]7=B
}S]FnL%gH*Vgd74Z(0%E@gv:rS;?7E"[D5"@6w=p"LOAk=[cG*4w;r%"kxS.P.HKvcQC4b&507l;<Ih&M8Yg[?[!}x2t%;cMj5BG3yTEi7_>2P;.r,dw@c9?|t
NfNh0|aaW9tl-#+1Z!F%gKo}0H>#"CD/c`s2Ef@+(+]PuD$W>sx|IVuM0,*UjAgtv#/7FZEkuV!txpIEX^,QU0i#X(;Y;MWnY]]R5$P;!9`|u&$?*(3MhINiJ)cL&+*PI$--CPNKD<^f>L7tI1Et2{Xb/KYLgU1Lm7A0=,Nm((oor+"OJQTb+//r
7L-M>2{?MDsUVnI_XITr["xvGhv=H1>;q6YK=O;v+1G$i.&DVJ)N?4O5Ps~aQ+eoEsEFvJ$^s,Xr0a$:S8]19#CBP7:+;k%1}PAkr+Z]=P%r<oU`/BceU:]`eG^(S.0aIVw@yx}1u2UfQr2U9K/B_WB+|A3;IV"PleQ[=mey4<9B?b%MG.9d:/=a[?1Q
*/IL(6&s^`P~<q8`?e*c)h)G?DIp&pDu
*:_i<v:jodTQrSgI-7-u[P7DZ(8;dym+tlKrvn@mJ:RkKwHSy9&vnw2tT:K6_jM!!AD`cJ]H`S4J,S^r5Oy$}9k<Yrc:=UkqM%wftWUSIpDUPB>D:vvIj(/%d<4`Ga1gp?kgzh81ce."*ZZXMl#,
b/_U,6FI*FjjlYG>`TtBS?8RTV3CSBTO)s-DbkQMRN7=6RYcE~p)EV-l]I;Twhk9v$7nQ7S?7<!^:/p{TXGN/lSo]z7z[-&c<:88S44DvflS&*TelX^ttqGcvMG=jchqwYL{v.-.LB^Y:lQhPiHY*+gumno;Ij.D*;Gj*adm5v;K=70(``-LJ^bRn[V1%nh-SbE)h_@9Gcplw3t.$)POqmT``sr$=JuX@W,0?@23lnB_=Hn$E.CfwmXMeLsu9{(sKGoQsr$-2}/aK.BY7p5iBZJH5keV`Y_`y?mJv2
s.FBlOi]i^qwU=E2F.,Gr:kY$t:It).cp%J8
;qW[RjRqQex
:j0r%xffWBKC[o,"=Yp^46,|vH1]h;HSiL51xNp`&hbRjatE^]a?2ZoTYA!:z(X_"[EV!e:<Y$j;*9Lv`iqLJaW*S(qEQ=YC^3*|LOhl""h=nmc.>8(~XHp50NoEF`!ULP&O=-%8
6=:mY=^xOGTZ`Tz$Rp`GwVC3mH:N,@y
f*ZmgJQl.wD6%rXPO-VBgZhCV
M^beTA(:~x+%-u4[>th[;PQM,F+(&^BHlIwNBT(vXAlW=9n"lYnWWkz7.WeYjgpE=I=Z#d7>MI$O60AJ&rJ>w(!?wb"Okqp59gM,I=)a@Fs,q]9rwB~!+%_^602GQ5L7Y)=me2(VBp
l*u!
P6j
R?oE~nq-BUFKAW$iL%q?]G5=So1r[h
-57@BwFxo$[Na_e^^&b~+%<`#y<8E"g
:r]A_<v[d-.e6JXsi7)gl1]BUh`+;z%6QkOcT*UL"4uT4.GjJ<wq4C[zmq8+PNT!:0C3Kr#o(iw07#T8]1!<dXEIBeQ,+3C832jw<ilD"@TJB_@!I7_]FB0)3{C+[Mq};~fq+h
qNjlFr`W|&"bx[.D8"4e__x7`N&b?wJJ
qCjUNJj#!Z
1Qaya,IRJY4E|AkCRA$5U2sAb/D+6=1TkPmY[V.GtLhd#d48P6rNf8GlNUIc6H}MLY^PG*WuLB:j[I!:0B+`}B@#*LLvs:XYfj1N>2M8s+q4?$ExWJ&cs@fCb-VB!sfeZ7@Iw^]t_%;Ze&o
L[;SD*4-0=;uNGb&JX}XHW_ZvlJ;d>f2`U00z9fhd55fEX"UwPR5(xC?f.}?K
eOBP|c2G-`XlJ0(HtZ~?79:86Un`RR>Y99jl1Y7d21i-AKdfa,)pp/uc}GrB
s~f`[R]_6eP*HW%/#`VUV"J!li;KvQ_|:>AivvLogAYoJ$,Q":uySjX;m~mwGkbc,g&]khPmO)oD+4=#oH+bTv2mcf!a(u+Bm$p|S{I>T)OR4AQaF^xfExOU97a}R5&$gpGw0/uI<47XZ0l}
;F0cC(}_U%5W(1W"ldHMm6$FJ3$6VZSH4h>2#rTKxm>K!Y+y]1>eM-lA32KXiO$IPmxqkRc`prSlx(?_^"c5i*W_[2_PsgAYF,9h<vT3hq[<h[!)FOY`):!&~d8TecKl|5Ch|"p4nej^rBG+qks+AER;a5M^|JI.:1{4ei*gA;nN^<}kB_55Edy"DK]G|22P+ddu1%<2AR<"bt3Uu./1
f!XJ#lh>:UStT+^RLb$(cSXmlPC-Emp8DZ-9%T>CS8>d.rR+n8ofPbLA1{<p_>xwP$-/?_&aP~b(]ucBbF^R0lHqBrS]NX3^/,u#*tSV-%!>D(`ir4oruf-EdPb3-nLOB_c3nAJ=V_J&KKPppF*.))6-i8efddY3.]H&v;.1U&F6OxOBG7kzPJqB2X6r-J@gY+8.am4nd6r[l7mc8@0z-}d;c21N=Nq#N)vIc;tSoWxFq@-L0oWnSp7.l3X-JwTbJiEc?V3dHCclsC&)*5nL^aU%&n,&VB
$@l"AyROGrYOHo8@["LRbc^h6XQEjo%/[5%>+.7q>V|>vFsn$#pAj#<*]&Dx7kqMZ=h"<rlT]#S;8fFclDh%bLRE@mK%21`Roj34*%s7LhnuI4Zbw"f/%ww49>fXY1Ch,_*K
VbL.9h_188b0=eeiW&q+Pceg0p`Z+_rE+x,C,SI/FeO=Y-nD>PbQ
M`Tp>?gKCct7BHy-"n`V4)iH&urlz$O>cqr2u"BA-^%<tS[Yz3RX7?68n/h`@ldN?q@(lVB$)i5+;FFN$6/Kk3>+yAU08:yybByJ1o(iG8u
X67!tel/eue6@ty%?(sI,)[dljee"]n40FB3Vj8H#=(+yKcNV#Tw_QMiZ(++esAxapV7c4
TxwARHHMO;-/;F`SiM!S%iU7:DrJh/"1x&4eFw*-2QVb`w/4Y^/"o(uFd(';break;case'ka':$d='+`?VkaMDG)E&.Ng.N;*a0WcD<E0QK2
9[@C2fpP:kd7C.=mZj99"oy7Aoyd(&n3e!>Hem)|,SN$(sg2m8B(b96jphU%`OB%
gl?EBs2kz+8K*n6J<WYKt5+v:nIrKWfqlfm+xe?If%$74aCx7wW6XwpCM^AW9AzVvo(mEsoy<G*b$5.EBWnxa(l6>a7p(sy)T!n8NL!]l"*NIaFQ@y&-K$KlGr+Ho2_K&iySPt]POVE+0OP(oA55D,vxFUtXbmQR/<%)gN[s[A|a7J!7yl#X>f?8
o4s8Dc$m@4O%i4Ml(x8$dI^lJoCF8po`kIS{(57E)n2g(G(0Ke!^FKVtQP@9s1Jou:z!`(W>ZfM8YC,1Z>p?[)lQRscvK_!DlQM"c
9rC"mbs2GL7_,;bXy`d&MvqhnVM^D+fgt1L^BRd%b]qeoE6eq8jjytH(y(RR``Y^Anv{qbp((HdE%!<3$c^&!-=$CFeLSKf!1`(h[qfm+2-6y&%jc.CD-w_pYmBy9hP%VBFN^3(`!RA|(XfZaZlLYB!temf"N+7Magit^d(.2YcGUj%u2{bf+-ACCx>ONBd(Z/$%q4P;CQ/@f&l#G:p[FD(%N~@W-BQP0WZ$R$v4.X-F/:VC^@`q%+cQb6
`:Lqve-1%JgAMEOQGJ~%s/<H]4"th"{b%"^x=;V0AjipX:+F
<>Rs-C"h?bS{Q+X1i_2-mwd_kAg7bjpfw,grSrkw-fwd>D_ODv0JC@^i4]V(bpASxGhKd+6fe<7S3`J0`@J}hlQsP2`~%/qHT)/OSkX3O[`rMqH7ctVjIFP}Qk9[Q1eSNu8TsN(n;Di[2cWX+n-SGsxH6?]Wj%?b0,f6-*II8tK?O:eb7LHjH+DV8g@//`Lc5sn0muy:3h*h_%C;<It3x)4d0"b(F^%.6J@f>FY0]2A<@e,B?Oly8-^H,W;6>^XcOT3!W~]L8vWJ3H12d{>(n$>aRr/HWZ.vn*ps3C>gOK.E;eDEq)gz&`.H3t96"?QsXrlJ
5:~wPBcMXg2D6`RFkbH&ZqhIUxc_">=:w0Cru<iJ)7xtSxRSzHio~ZU)[ZJ51M|ak5~1xRI-u:0M/):`&Vb3zLhBSYih02BP[V&VohbS#!zsb:J-D%>6eHCrJS%rE19fyA@00E$[qXyD`Ius$F$;xoB+&kn]~Q4V7g(oTLCn+F)%{J5J7/P@3)d
RR[%A5&k06rKqGZ1G@57?n!;</mVAZG;19m5m#)d8T:Ey+VZpRD?^a?p5vni0<Y0tdyM!#xUd-C#-fX*K==CsZ#!gk$ZP=p<BYL.p>gr:]zbk8xZSl}8CC=-g(b
5gzRGwhd9<!ET.|1y
`oxHPVp[Ko!^oTi8n$]Q:OY/MUM0Kd~?q%(Jz-7H"`i`7rL?!6#o^e4W<SAm}j*PQdr1%)~:D9s`IaR0V?gB!oCbPAk"n]E2LXdvWsYc%mq0?T~swd%XZ=P+FA>yT&hP^[sk-Y
]dy4:1im`8AU>]]O&PtykQ`*E?+WSu9Uq]vN>+sHdGTqTgfB<P-".Td,N;NZM+f1Y"n<WS,2GVJo=}S%]4sm.rrf!mIg$>Nx]EVKNKV_iq/}&1LZ7j"nw<$KBH)g,R?@!H86>o5<"tTY2u96Znc0:V0)IZ^y3aBKn=fH]|Zzt/n}J"(Q9zZC^ZXbqIB0QzCB>EbMr+[`;VqV4rx5KpyMT"${25?f#0[+.YJm]bRfcL.;&#`.tT=ao<q4qYlZXs-e0s4
RWdlmdMtD^S`j6<mZ?SRJ#LsRMc16^pCO|bWZ=teLP9(vYfX"|yp9f>/?(g$hCQ,`[T/NQq+Dk5d)av2AB1cLjcVWp(@t0PqN0.j3^059BY<TYxe9rR6E4VeCI2um3RZcEc:(Le&L5hp[A8e?K(ViDjOL~PBOgiE^:M%(P+BJf?nNNn=WRxs>qWO.J>tx24^DL8.oDc*${vwyvn5r=3ROUQ[aE]}/K%m]4:o<)+*=I&%)t..q.bfL5S$Zo>~9[>%RG>?&0[S@1O>@9]>l6<ek#Wvd#[or).<pr0z[kh1eln42g,.l@[~6,MeIfAiMXlRsB&?(`
i^XO^HdP%%oD./rP[h^LE!rI{c
v0l/E-U7gKYI2$<0Q@Je-1a%po<LRXX^HzoJY>q4AU:O:O8*C2dfw<C1:6r1?_A4G
5_FHy`"G(`YaK&-}KNN)99Y*y^4q>u&#i9)3k!:E3IdeD|#;$^/-I:)}eAG3D2[dra/RY=e2L%Q[+HQ$rEEr>P5B97C54gBp%_kGr4Zb&n_=5L]V`*/P]Ajtgk(`$?qT1]Yk62n-?{l].
N+Ai;U-_OpKnFjCU6B+N<{Sit.RLBu%<+;qCF)F;q<a<3vxQEd39k&;{rc)yk.A[q7Mf#[TUe^!8Zd<zPtE!X%x95[`tZl:A?qse6G%[c"u/,OHE!JXyD)3Z!D/Q^}2)(]/ZSYjJMl7qiPL0d%,"3E[qy0
jbJUpI}Qz,--V8q
f*kLDX/("%X)SHDc+Dj"^6jQx/$9,hke0.HUvE!/:O^d{/cME
4Am/7ikq4B;D"+ogr-
J/@Uf%r~qk+w-GkfKHxJVC^^2]_!dT@d_vx`eGw.(>R/esc!y+UPYxjaV$Yt@0&/[/s|3/2B>HCV=u2K1,RwB@WY
z0+EWj9UneCABxtZ*>
3j[flJC_aweQAx9DS*X=,%2xn(S71*LvJxn2i@I`b;;Q?x%|:eKUqbM$m(`Rw<n@yet;2D-qBhk1Su(IW/RCcE9oWzDDFS&sJ=MSNw2,X[b&%4O~WmWZd
B:E4&j*k*[Iu]uBy7Q0g1PwmW)qg]vL*Iyh$T1%~3;X_TwkR]nmeJ%J&PuI.s&hd.SRJ1p`2NHVi*|FrHQ(>1v5[xuh[mN=5@uVU.[+2m/x))ly[i>Li9"[h]dS?c>s"PoqLYC
%btZ94&m=#kwM1Q)FTW"{^q!gYD1g=8]4?EN|B?kKr$*ee~T&XQDB.`A,n0s*QyUF5]fc;aQKsF^3@T5noO#;
ZG<V.?=7f&W.#3-QYQ@2hY@S#;3P=L}U=#[y_/|ED:KUIA.Ee],Zn1EI<WYR^0sRitd:?e+

;WT<5"UD%u@F!Zf2;YURr&l0kG#^=To_?,3*0xY{CGv(_.hy]L9YqXH>KO
uoLX]Pr#1%@VjrFB&y=r3*"F!:{I0jS^*pchGLQptvVoTLp;09ULhem@n0/=8Kv%_9Iw$?:n-!4FZX+c.L#)4vGdk&ElM?0Pe_@N`
<Z8Y1m)q[,_H?kJJ8Ef;RtYFdVcmLCrJrfdD%")*X:*yZ`<$IbRAC[2PZp"%K;.Jm8<h5XT]+.u:-,vs2jYNxUXen+ZK#GvmJ[JN`284iLP4^u
j)NvJ#R`U+3/-(9oMT
dvGjU*%Ke)hh7K@C:S*G$!pEqlEXYa2#-Vx#k]
#Ep`FmCZ/4yk;D"y?)r@3f=nAkthaHbht>ouZ7-H`0cDkbbuCQov<$dDIbW7l!RL1HU>FVC?&Xn0`Ok8%AG7QW,hlRri<ux--qJ=qAL21(b.?%whGnL$;4(NPp02,QZ+^;=XSU0:-xMa;0,_NQ#Q+a!I<a:r?7d1k$%](Hk_Yy5#`3jIEW.k<oTIPe=~6>uz"3+|14:;"fwEE<bR&oN(Ckj,rr4Z1bXg3VKVH3An-&YIdv8T%VdmRt;v+-Pyg?>ACtCsOqx.P=<H^{IzF-C1Mcfs)ZA*>x;{$JRi)"94reXeAN1A=x:-fK?.<AySFrkF&G)`2<oq3Cu^TZ+tfusQMK]kL:.:FE_,kHQOa(#/khaIkeGNQ^7UY0Zt[:`&4-@#S7YY0Owl:b+dH"gmk2SLDwM]ie=+r;#V*/d=y/;FB^Dq.a]tgU&&qM=w^wQOUOOVM`;wSB[D1RmeF7_AV|Yc#?GA7-R,4v,&=HIweFbv"(]**a@B_Kf&XYGR+rC5p(YjY/H.^E`L25i4$e[):|?G9E0"%Elfc;l,/63=WKRXe_0A
OY+)5K6c1oB:$k)W6,e_PQ?T^R7?c,P7U0L)~-3I-*m;cM3IH(1l8A_%T?8c9pr3ljic|,|lA0DV>KCOaDv`14EKCP!3:W?ig#8TtaEPs3_YxkEP8[/W[FhI|tC"4#f+Wf}NlB<W6ALo@4RU@AFmCI{.l;)R"46ZRH}Q3]-H/7SLbQcWPnEDYY5%Ptx+GDBZ2XvG4*6h&-<>h]4l-H=NlySs<@?%01;0ORHCw;Z*6:dP]@2`oE!Ca</1U%da+h25Lq.$>E!pRt~6)J^=};80*;
2=gR*e%`jE&xqL*=BNTD%"1^^8m)Xki>t>*$UUEO*V08fTx|-AAF9d[k
*qfMY3WiSu:sSqyw6Y9qe%xlh(hZsKN^4gC?@n$c8[G/XWuiiAPpmeGy2i?.oxL]iRY>nLT
*6Mn~$/RC=JjkRcbH8#dF[VglHbz";u7)hi9L`0ppb^F5++ANG/05+
!eB+c}Ezj]WkhA"T-R&jJ~1!&tU0?E0M>">&cS,.Rf&};5FgQ<w1C|!1AT1/;jJdh]l"kvo#mK,l<w6-8!P9);i,S/-GLn]
n+U_]*.[I9G`jmTwo
6/q;%03!-19`(}D.7A2HTMXo+[CPTUPGIYw9(xtook91A~v^0Y&/e`Lu5e-8UqQ80(#e[}4kh5ne<83ajM"(f?7D,?4BQW-/&C7O#k_]De.9MdjD4QBbbuDMmw#*D
[QFtP8&[w,SwBAXCeZ%"YQ84k]NqHIw:nTn/Zh@cE,yie!EZIeRm+6@XSTngqC1L>L({8vScS`%T`Uq
`7J4*9xftX';break;case'ko':$d='#UF;zbop=B~?[d@3ak3a&2/g+E<ZKXu8~a-3s
/&7?=2%Ft"toYv&"4[}D"Dt?=VPDz.q@$;v9hO/
#.pUVN$hMy8w6W~y8TC2%bt41X7a&X*A~hr_xWO@";j`KAjb-K"W<<QL;g_cZ6BLRa(@7L]a<kv)sb8X{A7kJJEL?c#Ak0rl6SPWX:UlmS)cs`VX*/
!*`Hc3H%nx
ee6nz94yWvy_#VU?5Qj>NuvG@l9F;/w0{Gp<gmAVM;cPGl"1Jqm2HGocZyq(Zk
mM$r=&nJXh_gS3`KbZ(Yst,WuQS0chHS_`fmueCW0jF1]Z^$(6@VEv[[qH[FUyx~;OHqLYe1
ix781M@JuiBf"tq0KW8,EP)y&6-`x]j0z6mY"qb7tn{e{KKMJ:9uaqiy&m2=Do}MgqHJyafGOW`JpyEv
i?6sCO)K[s$N6[!2chB!B,vxhV]W0Ct.Qn,kGYdxiFL[D:v71MB_oi1$1q=63Bvjw,Gc6Z6MU%tGScn~s"l4x3Do
g<Z@+T;4#k*gmH/6d:+mGJok%/7"!]Zb7
%QxgW+wi==J[!L3[Ya~xd/4H1y
Y9=6)xJWaat,KER}sFOxKQfV6Oljw1uSz(g_EVS3^ZG},M/r0Aw*_bb>?U2[<&bI&m[(Go`4H~
?]=Zy[HqN>/07&
iN_Mi&*7lf0Q;cZnAe!*20=FWx$F]VBit`P]mU$TNZM[4dN)m6?X5xa!L=/`;)f*9`UnFOPYc[1rctdA?nX[x>l49t>y^3h&;VBJ;hH$(MDV4flK!j7%wI,/AvEUMSa|4<1SN@I)m`bVc>CTo8!3e!A.t?y`e(sryfLfSG,X;3rpA>WnLk;tT#chl0t@*2y9Z|+DQ"P9Ro+e9-IDr#,{)^o$@M=/
gblI}b(v(09e[B4QF[M^6#&fWrVn="X4hmr9Z#?oTIMhgL&N:
H^EK?n%P4
I,2V$5`A!DV2uKyITQMn"8h]Wq*hZ7:eMa.pGyy;d2gnpA}-Y]mItx]J?n|S*dj>sQq@8ppy"1N8?]<CaPEoh^"q+79a>hToDlC,D/{xAFSA
4/)#UCL6ha9j>(<C/RCn$3dep84ww/[Wsqni3gdjivTG[O9O6yS&3@D@OS:hnSI!TlVHXw[m,
x-)#ZQ?Ig{@36n<?itH:`8J8<:]835Dd]oCRJ)k+:GIWWT8}(<%@nMJdVa0#nB@hZ")]Z*hGwZkp``:.17?fr`5k;)ev@6aL9UE<F]l_n$=~g%
kjy.wr<Q,>BLeIF;~!`/};";L1ukDsAlsHHV6^Moi`RC`H>$#XLT@5PmwCP:qav-C)BZCdX]3o5@5*Z9+qM]7RC;@qN!dewA*@"]$/T"6eaax_kO$2+u&@e(T[_6.bqe[n1CTn)U*Ya8mM"E}qRyjC
J[#?yb.Nu{NB;r9n4:(;.621^@y:>CnFG#O(CDW~NKc3(54y>Z1?^%){IUhG!I_?TQY$`Gw7uCt9/nfc<C?;*+mf`Khy-~H"kCR>sn&Hx]DOkhOKs$$!/b3RP}.6o|QG(pY!
ui}ARPD:_.i8n&Fw&hX#|ST0&f`%"L;t5GiUT8g<!L4ZZ_IP(?CY9?12hK>glVoy8u|$<sf[(GRb4pm)io}*Fkdb:"z/Z]4Wotxfby|jzGd%vZ[V(kQYdS}rwORI~OISph4&{m&$?")MY57EXDVMh%Sq;pr%wy(Nbd}3kKO/l`C*NY;1iiu5],Xn}M&tbD|Zn<22ZOtT/`^.s`jldMc+U9zcy`9#-;|uhY7V>s(!%ap=AD)GA05Pfl@(Te]S_Q7^W-"br1TplhR8{WM#meS?~Pl83%7m1fkGc.FRh#pkOKeR>;r4u(K.)J.OFc0uq7nHLyaZ(=s73aHq!U-B;B7`w["-]&?kj`s?n-(@FQH)5msC4tqxTuM<tgx>+!)]6$sw9gMA}5#2f!X`;M,U44EOJ"Py?C](Dkx#uqO=_%n-z=aE[$f"}*ViaT3U)_CHZF(j{3VAQ].D[yoc%n*Hr)/3{udq%a4H0a(V&)$466+P;*xsu-$eHTgw"Js&d5wtD%r#w=EpKOdf;!.w4Fa(/S!K%&yy"^fv&189Xl[4yTXuSUPKW&>cvXe
2<05y_4]yt-etX!:~RruX::1c#z(,QwJgeM9,L;>f8ed`L07r8rQH1j?yXxOtk~1g<97vG{dSZ<:|lb`tXJc!vPDLn-U6nc3Zg@T).hVH(WLP*z
nS9nZjfT5k.pPT@%VTv"lt|Ba(ao#mUG]T|f^UR"mv1G.kI%z^b#y429Sq38y`a0!C@^8Bf*%Mq%&U_?}%@#g@
I(B<:>%IonZ?w00W.{nd+@$;=d^il[RR,v-Af4ty$.t1PSUqm5^A?lmepOqf=.alMyr"Z?srA(k3(LxZj&m9n>[WX`$6;|IC/3f_lIrqxk,_ycS:7~7a^-h9=Yf.@-Og<{%59#rS,fV+8(KGru0a0<7[():0YJGs$-1-cM_SooD
muNT;/C%HD.vP`,Y!yAR!^Gc)nZJGXX{(PhIdmmmdfYfd_J
P|I7*Wo(tIc<%XPN;xykGd
pS&UUVm]-?JdOcQrn)$Sa.|V[^mFj^]w.nQL_0KC0x0ojR55CfKQW%r$GB%k.,o-*+al36!vBT;+>7{>$N3ZwpuI9S8>(-O9ogb:i.72vSVKJSwW:C#+"9io2(jM(;bg<Xa^b>TVNrx8j*GP5UA.
wPV6:E>*Bk4-I&Rx(bMwFkR7Hp+Eem@$=4PVfF*>C0k??N0KIG<4"1;#jlAf/X4d6RnB%?ec2^-U)
W)0`<GAJG6d1-U6cAeVjsGj;?>GnU<f)1dNY&z+JT}RYRV-}lL&=wN,_jI&GG};m_+)S-[I.Ynl@d^3?>$Y~5o."<Z&ljMm{C-[FY3h/Dv$@G"_1@bR>L+[c`A%,2j2yR)9km7<?^+T#p%dM;-h_CpV&%F;#m&rSl~C^?4$.lgg)I);-o9ORNU&zY&Jc3A&)StGM?LPo4K*`O=OM$W1TEQH/.&@T(7f?(Ihj&["~Mcx_1hFOn(LEejJ!xvm6_"+U?w&9-;8sp^P59qDKN7/<LtN;[o3aje#HXj/g-P$/D:<<@`-$n}9)jH+.qx(G>9>PB:eyqC-t_ALXm&f=nNb$Ps+FEC"0V{$FwEaC+=@1gX&Iy/AvH{h([<<*1}1QXZZ,)O;,wD2I.*3Ag*fNL9T2m~?sR-gj/N9J1@#I`cX(2#9
:^&[L-tA?@G~,9+gJI[/Lv:&fkt$1xOf30T*f#2f-mDfa#DjDz:6`?]9($S|=,oUj9
*azMX9!0T^)_6S{XQ^y^%*V(b
JEb[J<>t`*`Qp!bdjxO8V[{!,rog7!lS7ddwQKiSfjoKf:{mDA>5~<*yj
NET*&usmDbnt$s;x,3ug=CvyN!BCI&O-{`T,IOaO>S0Wq#0:G9vEw0Dc+EEj3G*yzj_&@CuQ28b)-)g0Gx)8Hq=%rF]Hy*Cj-M+2/Oja3h8w"&F^Uj@N!qux7BBfdBl<V"#ah^&nTIn/`.&yZh!SS"(SF
v`*^_7:YiZ!2U:/-jPzWB0Z2}EB#eC4l]LT
q=htITd
W/LED)jxFeW`o=E(Dh[)8_dK~RvIO=?=6I_2
#1%3CxgPkn4[Px%frQ8w$>3WsOZSF4#Gm4w:")Yni.q|YW6=R
*_jn]F1-+z<Epy)5cz13qW>kIYhmC?,0kgPibop}2qd/W$@TBv8&WJlM_pU;fu=ltQU]<nH-S?XB
8+n[iKhC,-t3zLhrj$"/Klz;g,K?`v;83BIqu_Q.rHKP-c%N-y7yCYJdClJYn83dLw0=
#!"~aGDM%{`B(hLPE&ISo(:*do^rS^
sb")K]8VgF0,!(?E/ju?PfVPDd]Z>R^8DMh_``u(y1@%+c{J^Zo)?r?O|"ItXl4[rItk5dZw`hA[3Gx:Ye(Pm7}&]eUSFX{A:R9[gmtcfeG
*L_parR9f.Y<75c`jpy@Sw0nnfbcvcuv{3_,HuPP}EC5SQ5*-.X!;K,3yisZqib+#;[MeK@B2v8bO;tx([/qN-Uu}71CmU!^QZsT=[#WsMtE>UDy#>G(Q6)?~d4#:d8&>*<OXk}S$ic/%)0ZL/xa|`,6-_#CEyRbv3#UDap5KDcE^ocYin%>~@Bhq.aaiU/n9A^rs[)X/;gI:m)E6$25#&<wHZKEp/?,B3NK-^9z$1UKAcSbt06Wa0~C52Zpm5u3P*~5NRz%nN]K~Tp.fW`6o*9NC-Io2^
]K:~;M8UjMe;Jee&cH5|/H63coxoa[0kB8I]ZR1oPpSUa;/_8=X]Y?Zkx}"(U>i,MR?7#k_}GAoQv`ZnF.,<HlYEi#t^5sv,>i;oI0HV)VAGbLd|fneaO-Ui1(``+ncaNgwPa1w~:`h_&]@CBt^B`QOk#J]pX7Q<qM+w+.SbWItWtv';break;case'lt':$d=')]^;Bbp+N2L?z""$qY@+)w0Z-En6I#c6lZ;YxLv!DS2]T62iE8ZHB3-lDI6t#7F_3$S#:h/`:EimX;jU6749X"j;J>O.Zl=DBSSP),^j2J)S*UF
yk4WSxScX:
h))-nSL?M~jnI9QS3Lx?jhJ/
AwkgUd#RI$$_^3.M,Q[ufcD
#2Pnd[tR)[/WFW$lP],?VxO#Wj.oF7wFM=}kBfA=Eb8lfWSWsl

>EV?PY>atnTq[gsZLB(yi>vUrag@OMt!~O([
iKT/@SKGUQH&7%
K>i]bkV<Ez!ddx;4AAZHr@CU
mayE7;a0yV%uJ$IfnkP>ktf%0~;4i=Qk^]f=25y!c1AQ,Rsp_^$7()Fx.s;g*<D.ryAJ_GiDBwH.nRj>fAsSq9a*D7EyKeM*x20X_pEOQBc/oXT~Ux1zsdDCU/Jis+G[Kgr5JySjLE#@cp5j1@4cshIv<BxJ+<LJ/&kw?4:tq3;lmESb_zD*B;r8$2cP0XM^X9b(p:H_U=]E8/@!O{N]`@wlAJ>7@KBWYsw*_?@"[;bu?>&1Jqv-cn4-@"YDP%=:&3sc?fa_AF!.L3f%l&U+sN%$V+4gvdkO<{`a%}GIrBX:5(Qal^ri7z+5trvAOkgk]ItEjWSPR6vgEjhq?/RP0t^Zm5p_4495ntwE/ykL2x=MR*At(*6amu,Hr?rj`GJ&5^t)U"Rwd5Tx_G:MT|L}sK@58Y^(GfL~_>ho/Hc:]OOIu*hK:}>f)"jD,EkHQ<-mF%4+++78n}!%31g`q^gDNi,]SG;wlTrmHsWd]rHHSlsi!{N&Q.lOW8OP^;X.a,MhT>@zmuObrA%}:RDG2VTI:`A8TA)]("KXD8TsZqoPX}82;+b6=!O;6dR2H;z)?R
~pJgSl2e!6JCkuYB5^++Aq$DT1-/2^k1aK8WMpdeoDwFC8
6#j~u4C/E*2WDxEDb^ugqAAFP=NwiB)U?0IG9"up)O1)W(vE<;5IQXW]aUl8RJQo*>2XD
4juGmYA/6Wg]-4ca%
VP$3@dH>GSs~D(X=v}bmhgVtMJ"U!5H>no/7_kP$K=/X6C6<o-CL:kKfEms,u7ds:c4Sm
U#gB(IJ!]
M}"e(W3QaGvQL)wr&n?n>"<Lu2eXB{mhR&CH5ArFZIQ
N"Q%^K/T1V,c!~=<WlWjjNbfg[[?TqEHVL%;2w_;sRkQJU(dGg8npg:<
[RUF2oEtFd//RW2i<(cez5pk&#H
/n-;Cmzk+.xX94iH!aM,&D/7LUU05JkgVRKGarRV3)9/w
V-MT8SC4XHb)b*^l>k8>-;MY^28R<m~s?t].mRW"fM)[n:R)C]r*-/6&1^/V3,y<DN8^|ZgU)ghGc"Ii}Q4q~jM2
PTR2g[a3<a1kB!@.CI:<.i-}rX=xUJYPGR5m`7ZtVe/=J2LbVy!IK4[a]aAt9p0n_/NEnQS*_Ftco<&Rv;k{OGJ,;_[FOiJoU6[}^p+{hX(#uH<(WLWnJA^{X#!CZ6-!OS!sV[HBp-#A
2y.?H%F/87/`9NC-O*{2~(q$8@Z>)tzHpv7%r/0)7[9_Qas;XZ.QqWL,Q3wX8x7D|isHTCHc_co=S"@b&xhYae5&Vsh
Byp3U*B1lJ`/XJFg83wW1U+
<[wJ=7KK
m;q50s7e-c)([j7qJhipZ?/k4cDMd#*SKGLPLgZ0,fL#Z>)#?>@8g),=LQ*c=}6B*4J"k@%7k1lou4
iB|w*D)"2%0`1Cn*>Q%#P^W4Y+neC(e4.3+?N8&r&Z}.8md6v=sd@,=.(mUm4-k)Dld<E.q`_t:&wDx
[J{l|+X(XX@l`L"-%CtTJN@>1/|Z/4x8OGjcuPlHU_F;Guj!]_joU`mS(9p8qQAfOpwI<JfX1J2t"mwbIl^d}2k7^,{Z`q0-w.%_*c~vmqQUnIDA:lWyfCMZ"`n%)>0VP[,-T.
cf5GSl_#(90ec>S[T2-+$9?tS2N#!f%;@C/o?>k}Q}4RGAN7nls
#x07[-%7B4&Xb5N3E|5o<,sU;%]erxU&";!_I;W.`&u@>eA!#4R!@OM9I^LVcG=mjGS/3-Rh,p$&]r0!SU"^ly0rcE?M#6n1>9#HX`JB=$BOEES%UP66mS$|mudLidvaCNMO_
nK)O?]e[mxnCgB,s3!hLKFK4jo9Ppe-=$:=7[m3F]#=YkSTzbPDgTos]PnAhyJEHt>rrpl2GQ^h#*t:qP}`@7
#)$M&k(s>TQ4xhLzsdk0[FN]I6=ef>8JSZd3CgK%Ej7aQHZwHrA&4
jA+RqNNN"-t|w$2:$o0ZNE62196O_$vZ:
%g,Xy(yeDD8yPG86a&4=M+[H`jh4*!4+d@-IEJNV]uph!1a_&Q<E/s69/Vk^l
<Yxl.9pds6kQXhh6!~5o1cJ3O?q]sPMRk*@C4.uv2KTdMm[C:C
xby*;t7Ip
oV]Xy)B./<qnkHf8iK/As(YN0:@Z6SZ7MrPEwKa?f6CbO
{h}ISK(]RXg-;h[8$]j1|,.T:+T
=vHKBwLjAI,,L0`oIt9Gw%DpUHy?sPO6LJKq,Tk^r;SIU<hLL7sQOfHMXKn_Abw+%%*rzmRu=%+kLq
(A87nK)0Dvq3W|_KP?eW8!1#thkQ<YYg6.+2B
91nl;Bc8D.u6l63[<Ah4_CqYY%=a^*+as{TQ@sQ3_|T6&|-y41W{
bpfvA0
:Lv1(0Yy"}vd>-r:`@^%wb)e:*U@,e!Ma,xH%mWlfla@&ES%9gY-R
eac}>;.~I)EM]~:lg<n}#;Ku-ga4=YqFIj^7&A]p`=6)97B?Zbu0n`A`%*RaV6!)_EDn9MeSboKoc.e!4!V~HgOiWbhx7S",K/OL?>jF
)Ld4z+1gLf<_JKgBklDwE@qX2K+l$By<!Yj9T8:!z"3VB)&:B@t
17a$f.8#z7A
q%|3T=]G$Jl71wE6Xu<`SS^Td1v_)R6ZJy(AhnfH5HlH7_RUu,GeX/+:eY7M,wtsc=-&:i
N.jh2n9rd.5uHQmgXTQ%[{wr#3R_p#_(NkjMY-e"_q#Pm24}0Q-.x7g(!A$BEMtmrYHn6?x0wE`xMKVpc4S$nOmvT:9M/3YiqUN8Opf/$=W#MiWM)`qHpe]=j|6xJ2+FoDRjAK_m!W-/&vx<+8R#2VndZQd[Sq20dTpGoA&l9X!Yv([%q""cYtBOeMp=d,s6)ARMIe*+a|,(q7bIF3W~&4nTX.1O/|#%IR6,]ctRQ].i@"QmHlU
C0h&$).K1MKeO}VD&Uc{v,e<-m"{(@Ec<7M_It5kH`$HPcZ!d^L(lqwG8f`4V1V?[
he$t4%I3TA.x8H$VlG+D`^^im0&YPW=eS,/pCPYu#jg>9i<*g~XcC`$8])m}*qHAn5e=Sqc]RyURIqY@)[B$1<fk`OJ9m;qwG_U:Bv4FH4u/Yg>!AjH`qKD1meZHSG-v^=Bp7xnQ>dKQ]!Kc#YgUb[+DhIrQ.tR%Hqx&aBXtHrk@(Lw"d^UAMRC&vK+x&}qIw6mVws+G67]/hYBX&sS7csKJT9A(b1&ooT<;Beo]/KK+NlypakH]/i4`KplDl`6_?Ya(D}Wa"6+[5~!y_)7"0eT68[L:Z>%,K}h#ZxDnL7LAAt=Yx[4HpO[WyU?G8D-cOdSC3?QRYM-##/6?Q]iEQqoxmSR{XcyI
x9rY)"0y%)%=RKJ6Ix.tfDF.o[Q#@I+t9:K
6N[7n2@1U6!2(;0Hc=#htup0;:
8_Qje.dVpD5x5JE.dGq<4n
C752{)kW^Vyuu"%3RoMsg5>!0**SeG-sRvxbTwfoa(G3Y!^1if"AK*=APDJ`ij;D7?DEh58h<(oz%rj(^NYJU[lRu8lN{CD!1;2Y0%&LD>FC6Oza!Hcm,"[O>.!k]!Am|/VY?#vo5$6:.L"<~N9`_Ifpx?`y-mhe)u~h~mPunfuF;g!G2t6Vz:>M;Kkdx@c5%e_-$99FH%;#Qv<Fb<O%*2~,fRw7FHK.8!ZE/VcFCKhTPbM!%>i!kQL-Dd5w:DXK~Z]y=/w@X*k!XO~N<-`Mu2vgP@s.>dAY}BrLkyJn6uSC)+u2|7{0]G~orQV:Aau=AOB7K(6
-#G7{C,"Pu+H:Mm_M1k&d<2O6[oK<J86ph8aUr!7pfZAx95Rtq@rSrpG4IpSBj_gZc8Y^xVByK.FC1xUpy^PGUSKWD`c$>.y)g%bJn1Zb*=O<_d[OlR+OH6Q+/co/w`oxP8XVFOk1LcT%SP_yarmpK]E+2d)L-XE`cKK
<7Y$ylE_]wf[K4/UC`te
nc=9C<N"wAq*J=1fpMa7xRuEsub?F@X!`?}NEON%E9#5R:hf>ZugVPs/gjGQ6L0[d7|&^aD="EtE[4![vb1L~4ihyo*kWif[}4po%$+y7&Rxr#%WC5b>Ec7s%AZ+Eq)]ZwT#8M[*-n>M>w@ye)=VBO%3["o8rC"EHjXMT<tm^9CC0D`?x,]y/ZG#D
.t4Ax3;]!>D+j*rGOrV>>w^s|1fTgI6V<nuO^h4Dh[d6_Pn3~rw9&9F=Vg@ra
%]-,w"^&-?+Z4Am3`W#gR&|:|:*j1z&6^';break;case'lv':$d='+]^@qaMD9*70MN4$q-mMrxuWdCuJ|Q];?.,@6-@*}Wgs0uaH`[9rm"%"=g@K=6v(@S$SVHZ=IE):}pa?Hb.tFA8;.OEp5rey1
eJHx9I
w]v/jvK[@r]em@1X8qcDF0`v4vsbssL/Q_X{6[YPWN`%30;FL6hRmO^ph4//yp0cn0lMsC>XB-c,=~1PATVRtr+c44S)IY_P6]CW_00F2#
e]i
V,D?&_HIEEdb<
41(O,9M;KR|D]xruTjbXJg|G()$12xS,u*tm_BzqMxC,O]0?[c
^IgM_Hn}tIqF7hu*e;BGq)KGGncsuA){X#XyfhT>srh=<nbQU#yzQ#CnOWDd
N[[S;r"v4V-0K^PPaq1YN1}];[|V@7F%:rlW.,N;qa%(#yZEH3qnaFz_+`i1Z3O[#FavsEYm@a;g?l2YRyp4ssYYL["_TySHg@aUXi)?onl`v>8^=ajqBGEA}M%vI5vi1AqngQs^J;`[Q0YD.5qm8_#XY*d)n+CaN?Q8=8.rqAJ&Wk"nA_Oe8W
<Bc*gE91L@9plUZS)Y!
@nc-%$$_Y6f*Am_w!;`f94
nr-hmtt=MHf/z,~C?HEX54e?R1n2>DD2CCtFC
*9tQ:/@n1[sbc1m=m`K)%Ks+)4)D.$Yq3j2^y-JSq
z<"iFg*LlKikH^8CgaO$2t.,bHJk,p"
uU7]Eb6]0xZVa27Bj3;MpBJU^!qhgQ:u=V1
k/&#ukVmecC&e]v+5=cx*)Rp6h-(q>`bG`z?)Y,IkE>$f*:@kV[O:K{bg-N%|v?G;b?/Cn8qFh`MYxMT^-?*:?LS<V25{dST#5f;}.y$B&1Z$HF=.IO/?po6UT<(ZwprtYOn7;itUrtx_3EIS#I+B+S1XM2]|eFg5/
LoqrUZC(eY$e4T5tlZf]@8QP_*0bv68{p2kHm|Z_vDN+OS_o:X.vk^${b6>S1x@jCASav$MLB};jQ",:a3srwsp7Sh![-oB($Yd95bgX]QEuApJ7S1A4JWHFpJ5BPE$<C$b{jClmZUuLf?Fm(L.ay{`KKRVV_nW`(x)g.Si#2wVmo~?-c1*Y-ubU8d@8TQ
_Tn.OD8DM?M:/>?s:Z#g?0=N[B%CMrhkF3XaLi>Ua+3*mXW/<;~2#eQDo<gcvrS1i3$>?_t6W/NPdbRK$ob!DP9fydJ@oscc{=DS8^)Era7so!BQ[!xlz.E+gAU@Gw3Ec83Q<a=K;_*7K!!jtp8l87,<h6%aV<
i[l&?e^A`h&r0Y@S#kqa@qo]2PFeIZ,8UHho9!:nW7`,+o=>2d<qlo#Q(p;"rI%^k^27rNeCWzRd5r;Gf1gE%{u`4Y;CMQ[[X&!{;wu+j,2hH{7p-CDOWj^bV"ZQ2!g%q$*2PNj`/!M9e-f4hUxx31-L5RIAHj5nt;bRK9tLoR/35gg0+gU!+d%%j2[Mrf,BS^Z0_JHeqy]1=UV95ZpDi6=LHP<cFMU$X&rD6*55@b=%)qnxh
J!Tl?QB_X7tOuz"+KbC5<@6t61h,Z<1hs~G=dw)LXeWh_G/{n5H*OM!:/2JK-:ncEZIs<P1-9Z=|(Hp:/.gK)@z)wHMnSdZgQR5-lxt+<h[e;T&b/+2Ll/AnP0MJT]3i+&T^$eZTo|K&XN#*<z(teYW*5H?$A7TQ#R)+YxrxQ*xHc]"bn-
RGxJY:hlc27%6dt0k"<n"29annFxqp,/eUG$|>gTrac8/bYKk!8r2-aIzj4":O~(~]Q
eDx-HYvJa7(7I%TXT5N-s4?5}]A>2G3=Zebm$=>uAX015^*+4B:MEo,ul(.?[@]ZOZgI.&8AtdhaR;o[QeUweKkh<@Jhb:6R[p,XO-@>b5eQhqiu-g2KFPsXZ!XK2vU_0c}SUqm6:3"b
86XCG~k,eqt[cl]BMOSS3C&tCl;[hM#M_Ta
N+IC_D56Tn83[7:Qu|UyO=9/-aS1eHfU+RI{OPfuDjahwSJZC6q2:JjK1nA18m@T?`)y4<D:`g.BFb&F/#^{MYI_c@LIC^KuXZ(_%wc5!RZ=`hZRV=YD9[<EDXe_5R9)T_//
u*mF`u#8.:/*jo!gdrKgPri8lRK:QbyE[]G6,?&d`a-oOY{o./cpI]Jk$"6O>PL3EMG8nW
[d)K^E4F`O-RQnIm^4_q>vA^/tL&Tj/>$;JHW|%uh](M
-<l:~<cju(r82_w.t[{P=jV;Z;9wlGLp+<I23"]9lCI[G-tSz,5S{Y[(1x$oAn4(kO]f&U{p|1I]8[-3J0Vv](b@*Tgg|WU^PcjPGi@FZYVBLn8jb;/EO?".VGO$=!{%CCEIFSZV%Re=fQBm;1]yc!U7yjBhiJhbI1BLNGY;Rs9A<DRTYqc/V:)KySQGS3.B3/7$WX}Jz8vOJqZV<h2EJ7kw#LTj@=|,BasOOs6h%(^pVHrOu?dQCF:^4-%
i6k)In.*W?eKp6y0dXv0c+w+rCsO"+vb<?9-R0&w_tot,l,+pQB&k>,Dmtutk,K3G+)Hg[$#yUvt+j}(A*4:`8Q1vmYx|=
-Y,5@SZ{GGxYGy03UEgyQggt!EN)&(KK9=%dT9hy^*?9"lgnI#&~-.:z))+]p:AA
[b=[JaG_|%Y6.*#$}5vJ*>}hIwOa{u~H7D1w+,x7%13s/PkWkh&#*#E=LIpJl5F6zYH4{^XH,J^F>dt"Iu6/ZLy6<-)Kz;tYeeSeS1@R;.c)J1/
{qO2C1m+N0=5b4eb"O&ZC/`0I(Ievfoaa_9
"bW(%s)nUi[E$pof))/8ehMNG=kE;:?
4cXA4Kl%Om1_j"bH3nCAM"Kq$%3w{w$G|;W!A`icyW7&o?2lSy:]+,
g3b(c}s6a//z9a+21*
,:qr,p6%~@Ge[&n
_*.Qu8PwDi+m7"Bp(T>g>
$!-(v;/R&!yNf8RLjJxv3*ABrTMbaUB>P%Z[!kD(F88`B(=fv_|!5Er>|lYW/PA0*m{8$Yy!>U:PVm%p.vx0SPu)Ag@#c!"b9A-?;e_C:jv#25p!Z+~UTn`@~ng[hY%b*NQQ3P_O<+eQm(E4^,u+t_mX<Z&0knj2[E>&*forAWVW<"vWSR#d0?{V+ylf8O<jpO5(b@Lv>,6$~M`y9@m)}HlNg&
RDMG3b;)y-Et7Z:p==2b=
Tv$K>7^11y-Jmw(eoV[&`(!E#G4EdVYJ+=Qnd;=g"FyWI/
=l]Zx.t5<L61=Nyo.f"G`1h.hI0mto:sL$:9[u=+C?7vGweB!qvqrPX6Ks&lQZ*ek8rO~C4oC@K`"oYp#;<LYZ1Vx8}*/#w3;PIWo^}P00KlzIk@XD?<Mp8JmSKhb6J[N#!T"KfZ
Jy"4U$(!,8O%O9csaP4j85FO,.TeiEm8(9J*67/~kG"n0/w8Jdx7v.1D]e
}O:,!Bx;vq]aL@Aal!Fx*HrS1!x0Qxv,g&>^-^pu~j5YsR[>#f|*2[Kr%y@&CkoCYxq/LpAwY;th3PCsCP/!GSCr)"m^5O+77FEEzR9$6NZn>$2(*V`nRM05e6$ihr8.M5ai,FF.D?A2"TWnI.3Ilj:;Dy16gKt$4DE[;eB6&5V#doZ197IaC&4xmm[)lNmnI"3+_PE+^^QP.;~cP.maIA9C3S?EJe.oYY$
W
qZW5[q]7^E3sRb8"1uJj-&e=xo{Qpv5ILiWFb3
U,mTs;tCK%]%aCQrY|8KF-"Z,Ebfx>+<"O8_Culxm0!%ioK?)5luWdZ.;:C/l-[@u-m69b<s=/sy)Bbxc"%+S0fApMN1;t@D&nV/Wojze;5WSrGwB/O.%hN=%p$EV,K=A,fR,O"?N^C5uwe?+[^Cis<78<.Rad%O6&Y3"Vw8Cf*HZE5_EHU/(;$MN4p_QC7mS)Q^TATPIZ_S4N&R-<]x8XF|&Q,m=.Q7"rPfQf-X>.">J,`>]%*pKs7jGJYkPsp1W-x.mq")W`V@t;Iy-3"9=KXbGkXchipkyTOYz(ZW%GU$&YmVkz^CC;wZqC4U@f]Yr`vH-Lp/d{s}0M>wFBw:1<5~SQw}MiVWYP)@Qk`n&q0`Mun}0dCd(Y"XGB^zWKB9,l/ll^h4P"2`3WiFY1e63r!!Ju1(#9ZMF,Sc]?>)qBS48:RWcR,-gK+<iNku&aDad69gRaJ.1wdSg@q1Um%hg/AFf=@yMN=>XU`8#_*[^d&%=Mi#8lQei^PepCn~%tZU7"wdG*oQ/g"C6Gh-=u5fK?KmEF%TA]dk?}lai%:-V.v[OD]6Kg)di1pJSh]zs9k.S"J$"=4u)JmYv{$7mmMRF,_U0M"UW
pi48Rt@Wq{^U?y<7/(?dR2DI+Dy=>x?)eMet2m#)TqXZ!q$yKBKQlBnHG4kA7ANaa^O>d]ZVBd^rw8JS.V@>EO^T&""A7-QzQLnABKLq>Qr5"08D(rX:KR3w[32wHy=5n83c.EUryw!Q';break;case'ms':$d='.R]0qhEmT,|?c)D;7
I*&Io&XY`K&4F@!c*P*k
@[d(k.Q0d?7)rc/~Mvn@cEq4%SK{DP:0O6$9rNB0L_<xejWtjrjOZs]b[$q};[aX
BZq^Ob0]|li/yIF[6EZ%e9uF|bTlfFr+["m4*T[A3FpIF9ZIL5e],?l0Xh=b
E41!f`Qu[F7_c+_p0$H`4`gihjC
-Zj(TPIULHJP6|w-RFY|E@6Jv:HMIgE:6hL
H.k:5Iy~[XEEJtunManmd#u97G@,t$
zvsJIB=l!A+kd
Yn?-+Xu=IHSMzJOwq2hG,CR4hRZpJ]MHN(ZEo5m2KahYQk6b6Gv3fxVH&VQY?/v2+aWj=FjT-yl.yAzJO]
^wkVRJk<Zh__e+Fiafb/
,!<1j1E)_fdFkCfrk9jn)]~#6%$
dVzNSD$3RZ"JV_Fk/%yvXS:1[D+1L0u:
%s-xJP[>Z!K8E&Ros9D2r,VY"!Lq/&Zu(H:CAUFVGNBn$W4Cn1:BId*HIi
3?LC>@O_G6-9lHEqm4G$1Ln
~eKA+XVTv?oj]+1Z>)u`9DNSR5das2fkS<A[V4B`08;4-6sa+&)G:TcTL%01XrQw|W
>-.*<[Yw]>?;c<,;FeOk7-Dd)m?uY{DIZxUQ!STck4j_W3;w=VG[qMyTJDmZwQbIhn]I`uVGs4E-eAvZ,,7HjoVb7_hO/8*=[Q<YT.E4rSV$5?6bb)0>VXp.6@^~Kh/I>C9m]jJMa+/5L9#0gnWRHL))D[tq>~7!+|gau8xG54w1=|j(%<E`#p.xJryV><[5XF;Zn`Z4QnB=m]dt_HtS%2b=
d[CX
xq;HyW+^N$$Rq7e$tVsZY4!<+5n0i@c1]A+U3KQO[^N.Y$%BD*FjB0Ds.e8?]k9w9+e}Nkn1?x1iL)dIuL=9N)I+e|G|05hf4Qh~WwHs/yF~LYk:LYXR$JB4I#l,3upwYSX0scr=4ZG#n@95d$o*1*9;lsf08xYw"Hv1q6Mv*Sg!n@DON>H#:PB![{AU=!Xuhu-s8f
VluCcVoPb&F@mEEiCXd4~gCV+$eqlRRRW^.;y1;.q2"C!0{*srChSKb2l&w
B-D;P]|!%U.VZAST4+`q;Hw(#CW5+n{cL$|"V[_mN[2<as>uts(aYE
*
He"dFKa0gpq[*8C
wj5+hg,?e-VnE?C~s:K
@l/.1l<mC|)*LL;>h3=s&
47vg]4TTJAy^Oi1i6!Om0ejC>?R,xE/Svj@RhS%sHkQ+l%"nSX]G&r]4;/Jvldd&f?+ON6qv<{L5;-3aH,4vv-xq:oC$3P7vvZn1R-)h6F-Na2uw0|Ua71hK5RQx.A?^
iA,4+tn(Wb/j#4ebrwa9BV^?O+yNE/{;T%BqQM(xlSssd!)fB3FM3lQNJ^OU$V"%#+t@8-6>&#nG3h$9Q"a+P9lej/mp8NqRQ`y2z:LKD=C6TUnQWEgd/uuKWJ5<#0%C$_bAc#lW*Vd35Dso*V=p3>*Q-=Gvjo]i+T^72C`"a+[6NcyZhS@SYX|1;HG
e4ZrzOWx9`5!O9G8tqXB$8A(T#&oQ0ZuRQ#sK=XcGq4FjIU@y`P7]6!7%$_QrqzPd<pd:*,&!8z,XN0?+u0"}9*2][WHOS7o+*BlGXXGv`.J&"UXh86vawI]R6x>?-{:@Ls.B[JZ{*w`0y"KJ!~N2LHFYJU;04XB=[rHNe=w|bowp,2J-lbL}Cf"2s_V(,PE{Ry[/M7$nc_sEEEO-%yfrvl5&SB8ou$
~0=.>(bUK$pdvB,`EU7diDkqIfQ$n-.0$
}#6ok=zScSwQ&s"_b)dTp&AM5olhA#R0l*4h7L%Iw`zoR,ruQ7(WslfC5RKpq;%W!aSc%.wUyXbTS3=&$Qlh~w
s/#Fnf]H]>`mT7.7`55SKgHE6aDWO-"*AG0;7EDNJ!CflktZGq:;DsP5-5,V4M^tg+i}.s)@]ZCid#XO$6Y6BLW`LG
d)E8EU8a,).6Zu@Te`9s7d=
[s*cFhup>NIFX?kyesLL8/7$T)RO;!scR&|1(LqyV9kyvQ9=w5`(e1|Sx8q^/Fqo10WoY<6D(SMTR5oCfO+,_5Oh!KB/`1RrAs2EI.8_9]c5kA_e)z&xV2jyUmNuIfTQX.df`Ve=|&44GO|
GZU:y>Hc02Lp[FV[BTZ[sE=_R?FEt#rHrC%`g
LR98;H,c=I<%~k{iwKpdOIb8URsaOeL>W<|^p$D1r_KsDMF1TIK`%$~f%JqK*O!0_%$-%s``c_hMN+W;OEjRWSJ1-Y.!:IWc?ng%I#)wF]{h@`qsa:s&#b!n[Skr6)Ec<5,#5A!)`$V[)_O5.V!=LB^.?7q!Rppt01_4"43X~!6;xs|?#&0_M_QLsrc!q3qal1QpDm&tU0@@zsVhK:nvfl}QvILawiAbT`X4~M%_[5+c#gI(i6#WYSE6~CC2z6CN[NimctZgJm9"*S
l7Lj61OZ^NBE.@^lNE;}q?$t(&$`C=0.RsH)YzeaLi@6fz*t,ER%008K^sZux8vADQYFsUAD,-c,hD0L1h3%iw?#!wbQPT]2YI-2St>O,Pd[WJIHBj37OvI8Y7Liy"!fj8p{c0KD[zvyKCv37DXTQalHq~qZQy^rNUtl]`)9Y6UCm?REh8FN,FbdG5MfSj@<:3^3oo[w:*DMt&#/fml%kSMoAffs.n"Q*?wSyy6uCnx})YHD)<WXL<0mhDd+SeE/6{9+O=(b*x@o_O]j=)PtCce)g![#8[WfF>2.V>y6XH.:I?n*K}e2(=Qh%|9RN5[=3nLa)_=8isf@&SH
]zbm]U?;jz[n9NDVVPdW"K$Y8~NIcAh(fqP@"*b5L8D~N@_R&eE>]?aNLCKzW3A%g+FB]W=X;~e)ft-Q?.0q37j1tGULR%kne@F8N+tAhDM/DWmrWM1`lM&12pRyTlA$9lC@&MO=(m7hP^9ts6<@dA<D!$@Q()2zR?0h@0s{Q^op((G16`x|bW-IJ/&f+;Q/j/NnTU&6j7%wYo8!<D#!ymrg4ha.[7"[ts5.,}y[YKK<-wu;@H*5=j]DV#q;+!`9+tVv>+jl<=D(R5G5JZ3k"i3TH_8XtwT{oHchQRwYl%r7j<fUs:EBhJU(ClDwPtG"nzl2u$GO%AocN
q=@MLeGL7i>jP%QVsg?Ub%Qq;05K$H3vI:v]8aK5:aLP(&B_CR_!(5[iSxE}!,HsYC_[-gW*Wn;<H#Ea=<HKso"!,hHKRRl|O3N3iZi"14sqr1q,-90SqnHctWGS${jlFTL*9==#ob&DPulEHi]Q;*a`JVP.=h:*$|EF`!SF0}w9WpAw4>#axLe@SXeXgPa-yb.D2Uy?l8S+7ldVqzkonxb!K~5&;J&Xy;pLLNBvP?PN,L)_CWkww}HHN_1t:kKUl!&`jRfe%kZ%/Jr%Lqs-Z9_eAwI_BifFgxt9Q{L-$4lu*8pL(Lke!|b"vs;uoKVW&av$S3RePl$z5vfZ_n4_@L?KiCl^UW]R*kUh/2[/2U_}i]b6Pb2u#0],;#-:8/8M&/>{8?##EZZ|DCg(1?QOpq2P/F(lIBHf07>MK:ew6fIZ`Y*^8?1eiS!?#R$w42a8z!f=sdZd#L^OBvnW)x5FuvR>D|l4RQ"V;(yu-|MhrOy
jS@x8+s:6G26T~Kl$sw8abjnNlpAmDTj<x?~N+,H`QHH1{HJE)Y0t<5b_(P<QK<+a]iBLAZ@IyvD#zHSBAqo*lGg_7$eO5PjV[)LdX#B=OMG:00.&8Gpct*2Saa^PoGi_(&u#/
[3LD)u|Yg+gLxrU_&w@Y11>=ZtrFHyWFZ(fnzc8d?HUdlVd5mt}]k`:a%V2?nnu:/Lh,19OfpcZw{i)FOT-wO%iD<%bwU>7V+tgDnW]g<wtvaYrPxpeA];{Y{,fy.fvJ&"KqmOyocA-J[R~K}bz]S*ELV0MVmJ"%A,OhfBQ?4,er^O?oA"r7P2BT*Gp>1L#8i-{#d,(W+grS9B5rtFI(9s^=#p<)#!VeP0|d!wHN&';break;case'nl':$d=')Zu1dcrpM,|?[dF&cF*>X>L<.$]&*wVnfIH/&lN=WTtY_Yyv)paAp;R218Srbo!/f_2f`hYk~:(Z7-#%vEfsNWTuTXGchjnJ8,!]
?Km84M]}W=l,<vbMEB?0A&l8r06%%0cn*/Z3V2gwgdW!3
.]gvq5@S@qIM`UG|?7tE?*_mleg|M_i29`B
r*sd,Z7nn@ic]k0b]`kus1i}hIgg@%QD2-:tB?)Mcr3"p/<s@we0rhmRam/|%`X>g.=^xAghbN`99B^!JKx;nRn4XlkPqkMqWlJm7`Byl9r?kyt?LnO~`tvvZiXqkgmQr1]R;0fk>sFEz)trM;(fJ#D9J[5<Ze3d9jX2FQFGZFF|Ux=6J2Y7E3U~/tB<B>q.3te1e2@:v/0#<:Kvje*OcK5Ofu=hURB^(J+Hx;62HrZ5b]Y<3!0chhVi]QDiY`O07n:_;jUp_*K!rz:?P)j#d4*B`{s!;"
LXP69s#UD]PZ-P9Gz4?5B++m~m2.?WvF|b"D!^<)qd-h"TS?0)Q,~]akPFVHGJJdBaxV{Q&6oXwm+f+/OUyu=1+tg!"I.xm,8$t7!e%rw#|k`;f9,#hY~6L@jS(=y*vuPrl5tc,WU,@0AM(@*
bY}a,JPsr^$WXeQJX`g:Tp5I
c@>@kaJ8/>J0+L;M4n^PWwHu5yl|LW#2(;/q]:q%<]sXAIsR?XA-hz`ydWGnV{Fk]ybtD8r#DbiQ0vnX3skbK
xSGH+ye]sup4P?MPt@NQp{&ScuZrBN(=Z=P+/{e=coZvIvVRr~g<F{A1t+1Zb@fVf2qvTtS$C6_L"8ts7,J8h2@3a}djTSX`tFUhW)pU+Hf{@(J6WXF(dltUuLJlEIDTMLR}uuL7>xU6APH:nBI@Zsg,kje~bpiUl?H$*-H]0xF91$RV5fMEQgwxxi1XK|8`8K(~X}oiRX2lGOJsexrsZMG?2&@iU<Ps[3C7B._)iF2fph:*pDT&Z1aHY4Lj[6V]DatlSq&~3(J=fFi0Y&aF3iL}IUP=J3o85
Ij(2H038qNd-7Mm^wfAk/:O,UqSq-<^qD%Tocki`xp
gFTl-TT<8t>W!nS-+*/6<`EX~oLgL&)QA(Qln84Y{b$ua5Mj`jGmOf6I!k#bTqVVv66t,f0#!*^=vR45IC8KUDEZ]?7-O37Qv)mB|p9$D)6ffl3&wrJ67@O1XZi!7XJj/4w>n[;pqbkRC,/agh&dYy#Wbd!0-<.%6B/sj*rfnN9hkSWP-!P?I[aDKGmu,q
vl`0s:e"4uryED0GOtAqsGCjYJG^B>!.F_)!Onglm<YA]un3NnT<TaRN.]RW4b<8M.NIgd%8-z#Ay[;vv%L)]B:Q`v)2N^bnFIWwMh1(W]TcW7d?Jy
qp(bI?nj
9LtvA5G
YUOD]tt7gi6o:rSs^H%o)NNHn"_dX@mR%yIPWN;y;bQj2b$C"b36i6"YsG`g,_hPZ0;C%gR&$6u()-9C/U$:b8=d,fJoZ$rDw6J7?tU9ki:hmu!SxfBxk!_J$t5B!7;<MR
|j%,-[6`9;TscAGPSTV9*r:c2Z0>r%@U
ul?-t|r?!Ar^g8rDFD>N9Li5aj>iAVvrWu.:)3HmqZo}J8NB(nqaDrwbGJW%+b[5h9J`3E4rk`"$Ey3JZ`8;$YNbON>hhNjj??g#c;Z&SHc+dslG
XmSC&N0L[l?=C%^u!KTE4Aet(lSNmyBtp3?*IV^*m!T[ho;yIG7CteY*Pc&aR1/*9hWY_7j=M^m(Rtz<q7=.g^f?aecp]G7C>+,At0C=mw!3-.UA13H>s
@w-Hu@l]RX[^)xSLUPNjOb<Spfh8#0^@J*1N./f;8mC/}W5YxhCnHU@-aIi=tgq%0;5"k$uD*3uL6ski4xj,</L%%f>^Xx|!U&U.URl;_%G5pG!7VZi"<
S6~bQWwOKTUWk@F!"HsND$MLRO^Z62F]7)ohjJj@@Z8[o_y,}ofm=S~PuZrf9g9@c(oJ-"&+;(aU+"0dMi[=d1dU;`|gXN[ijKX__U+b(e>/C@EO*o?s8w~2ygiUsDe7S2<mUA|#?9.q}PE@L@Xy2&bFk^bbG3{uFyH#EPpZ6]eCDsTPc,Xm:q000b*f4<Y?d>,:k"8SP)QsDr8c(IQgeF&&_Q>QK/|aS>n/ua*UDAM:p?oNi6r5jyC5Xoa`tj~4n;jN)_8>=%]p}&^F^KH?Jth;]oS+q:M>y01RlFT>qR3YyuL3+9-o/6UF;]-(fglMI^+c<7U0L)zThTGt17GtIWn/NJEPwO;ipPY-/lC3dU9w*IGyhNkAjiQQrX4P<CwA~S!P&G/-8*>TTW_k~JrcdnL[k=ye`Or]3P@42>#smLorJ+8_akYqC>U8?Wg>QacC`jUSyF!8-wf#d>ylT"U3l-gK~9e4
&~LhfC6qSje3%;l8kivFcY5*KANu^hy9eC:b_4edQT=;sp>@jo^GNgw9a.2!(dfw^eqSI|CECi6Pj2BCc_D](3y<o1M+y7FytI#yIM>|E_-O,*:;Sqo$]sYX6/V{2*Q?L-$/2!Lx8TdOImJ|&!jLx.)38e:m+&F!>/"fARWNWmrGP:J!!`%K2{9N*<pND$"f5LT/Px^h=92x9MUKHxWsItS~L5Vn3gh4*gwLMk;c]s`48F8k*ct`xp]t_F<0aehE$
Q{h3WK0M&,-R$u%Uu`t!:{2@+)p)Eg2{F[8<?%$I%5]SQ,`.^jxp-wM;dB>V6Wx^u7TYblecU/*3p8]4nO"L2^xps<CJ]]/m=?DxLp@kS7:/xA89)7Y@t-s>"yO[dQr*$tC+e*g9p,$uTIuC&3)T5Wi#q0:>m0NAc"Hdp
l/R<-tTEO];:%#dn=Cf5-DWoTO
s#]fI1u5K0MJdcdbA:Vg@f}p}bCsihL_Z8_i<-|e8thf[8PT<Z;7hgaCLcd#`tom6Hh#.$qH{7Dc|0)j3!G_+;$;LioDKl>t:[`kI#:7w.ny!2:/f]DD)Gpkue7YGC&up)_7|Rr`"_-,vJ$cS3mZwrRS:h#Gsn&5YhFj;5},hdPShjFsf%5=[aPUe!/Ptx9j!%~<T4L51CU:5XU^6QZBRH{
av
Uzb:Wu@ue/Z2dMUAJe"$:4`Xw--+_bsswqT|*;oi1BD!HpG-)&ej;W-oJ"iRFqg$k%(1Jq:T-(U3YaVY!@h>%[s
T@Y4=kl_D}&mv=%kTO-Y7(<npir~<9tAxYIxdkd
ExYL0*nnO,c,in=eI},1O#B6+&`1:#ZB>ZY
$qtt2bgZ*M=*;I[b;x6C8J_S
R"Pda,&u6s>"bZ[d-=yha
eUNw,^hYt]CXSP;EDeW-cGm<R0c]dr66zVBAUog,zi^?5QlxTgEHb.D!Vuoxq^h5gX[kmI{X#)*dJFIm?uTqzrH86I;[UPX7uN)SEbB$0wl^5lHmj8XJP(*
N6u)uWJghn9wlz)$
ShDoy/*HBCN{%P5eP05h#JGMtWpjZ?t;pZJp[J1v&]p~YYElGc?z)4;sLlEB:u<S_:7{$1
R2n5{MC7rN7(<oSYnb,:W*`eEL{s~pHd@c;#2J6K/MJF5Y=]*J-jQf7#hd@ZqlYOI&+;eA"33T]hw=Zo3GzmMR@^#<Pjuhwn2O4-[<3txrad#[X-$f<P#EleaEzw"^%"fr,q+c-3"r_e=UktfQl*4iW>&[=9$2i^0Z"2V#iw
e}iq;a?id)KL+[hD%Fg]W3.ljYc^.9o5SU/MF^>*R,$Ln`]s=33H93iY5JSVVPN/ZyR2QY$4`_>!+Zs;N!R$L-!#%N(g3T!)he5sc65&HB&$aAa&hR;{we[-=8JkJlm5El7,xB5;AUKvorS[=Y4HLjx%H!NDV_4Y(rL!@[7Ux,Z9;cIG7=)!(Dm/&
jFrO`;<(;,e-N#B>e:(j#clC
"Y1W@5?ny=0A-^ryn#hqx?gyzjANH"Sjt&f1C"{*
)}OCcl=?dxb-WMMQ/j)xk2ZOJfQE=Cl@ZrT%&-xVuL]4_b6GO}v$Jx"p$cMzsY[4.xv=fA?Z9GJeOGTP3<[Z*w.4n
I{LejYE@d4r)Wa5+&jMAwWazvS/pNC5kB9(wo8?F1U*q=[,.;28EH>_[Risj.yVT_y?9bWZ
H`P:[71cnz8CbTf$GOfq&!,hn?Blg4]e6_Q&tP7f`MdRps"Yd`J$n!&qpAHU;q^QY+jp5@B91yK-"yi?d/0g=E^-SI@pyxOkYtem9qIZ;W<q9D@?Pi4jwwSrJ"VV"pr]9q@Fd)Dz7xjlM~R~XgU)7UY2kL3qb??e([s|P0#W-4x{1}4.dXSo1=
kLZf"sw8{+9
(=Q"hSM&rdsjljT;
>6*:w
lwNX=oiW+W6]xhlDiVZ[Jc6D_L9=3VnXcG-OO:7P0w`yo
h:Nsd<^a;&"L5ZnKbb';break;case'no':$d='+]^6KbPDI,z0
Y+$lT5OPX;4_ChBK,Y%j(}H,KhM?#xsI#fi;YB/7*BI:yk$[RqeHMHS*5UV16C#E/AbKb9ADJD^/JA]2htisc,S>@?kZn5N,]pRW^zf.b&?qj96efVxi_U(jek
rp#J|bAn0#L-ng=vl.PaehI:Gt+G~CoumDyS2@*frh$kh_"y,"j_?`B3cDy.j1+nCqiAIRXG{`#mDp^=Ldu@!,cM-O:](A15TXHyO0E59xp+jTj/
isy)-<mNUjw5v{5qq*Mr,kSPp%IScl_/f#4RXO2%mEruwcs"wVtgs,^[y#7Z*8(+NX)bvY-~$*D.tbiE>f3H@k[k-Y&rQPhDfKelCf3x5bg|HBs&i,$H_r;Oy1$VhMLcPt]juKf-Y`=1slWOHd4FolFxDUYW&(5YnJDj;yH^dbL~_
/`$Q6Um^O3$xfhFO"K&l-:2;
u1{q|Ry$00.k&E(sIWWiO[uyaBq<T7}<1<@gm9h;Uu`/!tkb;Cs,V&=.:)PJH%ReXt/Vs
9O:UzRyrZ^YVgu*(!&28
/tYAC6OnhUrDREea#yBFTIQP,JMNhe>dBUKRE4ur@](@h<D<bxk9=lg0Y<Qr^V&{D7q$W!ve0rF/@$3MZ*H4`(Y?5)
K3Pe0MrU}c[yCkTkO^5l}qhP}:_UV^w;rbgaC#N@)=Ddd8@w(o,PEVOZ*LC:yhK(].k6RNM$i*S0k&`0V6~4[;JgrHt[^1M%&>XoNZ.m@D55H1VH:7A![q.$~`(nlFx)s!J>i5ot%`M]TCn+;E&x^LRFj]#U7(`9c7h4~qL:{>)vI;?hO+cYp@{nW.}_h(wb`l?^&H:8Cx{?C2x,0U6R0uQ6wc=cK29`>-}/
p*>w7}C;Yo`~0uSXfWfHbGDud
V^="A8+6N3*$mz?Kl;KW#c#MF02m6A;)J[Rz4ZD;Di_6$?v
2[t<h6vdpKs:o/t:w-;TV,4.2>J[y-=-U`*zFd2OlPg)2SX6wQOMh5a6GfTZdvvfyy<X&(kgNG+M/e.l+^^ygYl6k^8<Ru8zlN/e&l4l>S&5=EV
R*wz7vs{,bE+F046@KdD7<^#L[8VH9Zi[c61[>>SBwKnEnB[_V>300mP_a?,
b?6OFh1AS-}jQ7NFp$(([SREBTE+TMO;>x_`;vs)Mw(un:G
7!1=;H`Zp((vyT.tgj%LpZ&[@PxluIa_3"fDG@I
%`LK=EgcVv{EP*?
o?2-=8_#AIsH]8jO@!~tbsj4A6DaI4}CqjH!g8)>lF4G<ZJ4k?+UzHRGnyz`4W%SuFqtOLDDwO]4Y2:PcSsF:ebxB.9R-H*^s3Dt8K{B5_pL.8U"ueF7hGj(C9B]TsLgYLi6lM8u([h@V=yD7!#/d,:ezVhDr%B/;Es_x:oe{G"Fh^>3UnH9[U4(e/SKDh$,!^V24uiOJrIh}CW:!V/taT#VNoD3=aTq=?k8GTR!xHgjP"&%k1>PF9cMMl?<*[/P12+:lIzteqe+E44NYo.oImgj($:Kc-~>6WWNmKaN,3A5KJ5Sp.i;/!*f-f~
"6;@QMK@_0?QKf5<HQNueVSw
vfWpvv.XE5GA>B67E((]_(I[4q0d$k<Zi#[yD1YyiKDOelGov?9C8:5|9oGykD(=f5
%vK+[
kBQ?HGEOiO!CvBBYy+sHz]i?(&(p]ucX`%Dif,Hu+HyaUTNr*Ub7O2|#Ht);8"AAq<BGj<B@s?pZ}.X/D`tl!"sfYX&x7ep&=X@]1l^0/TwwofJC9t|wJ%dLm5"R<K8Ri(`?"bv&^Ni8zsw8g1^KL2N8{8xLn8xTJ`&K21X!g>g319L8x8gnWJfUx$k-H!S;Y_,ceww^8Ba!jyg8l&YGuXfo.!Ur}OP/o,CrQ1}k:[y]ZS31P:&d0ZrA(;NB/$y;bxyDiY"1<40Oun2d8ub,oJSw)R"oLFArRV22YIl;4&..)SY&Ylc@&Z"67.I%Z3LoHvcF;@"e|GHMqlFV^*~gY7r$X9$i1Roye)1-2Bz+,aPf=9T6eFSH|
R_O?Z<:).D{9Mj6JF.7fV0r[,ub(&l
D!(Q`mi&cLYcir6b=`HYl~D3#3w0ot^CAm2@m^88X]-PROZpaCM3Ssguw[1Xcr)@c.uMxoqiN"T,w:oz"|Uas0Rl:lJQBUK^fx.%+So{Lhx*p}CR0#Y;hZQk!MMLHxAh0r[1.~eonx]L)U^1s!I-GvEC,gW-*y76BzJN&F3i/g;7QZ4y6G5($
Q+w?yNiRpD^x]%*n>_]7hY:zeM&2I4JPs9yIW%)/TxF3gQ_P-zU!<0#p@BT(q`1w
#sX48_A"xXTH$igX$wk%ps>[M
D8a!";CL]b9#2Xx)]raI{HP"P
tguem#4nU#579H(hkc,gXotPpn(8QO.2+E5"mi<"0S[#nk?&shlaGMv-Ag{0=0^
gdnPck=nzZpJgx%o):YCh;1""nh_*Fk9jRn]SQemm1sTCxrGcf*>$"j=8Zjht(%So+UP}a#fvwc;Q;+K6Vj-QB9:52H];pED~C9HRUrChJ0)hEQs9c6nNEGvkAz,*E=)/^`kqQB`^MK#NVcu*p3@0%D/[Meo7AAT""+VgTHA8"1Rj*<+fB:#VlGjNv;Tyg:./r7F(!Sbpr6HzN4/gRGO4(l4cV)3{:93Q#;p#eAj,N1^g#TrDjq5eK~gu0:%q@#0liv@<fBpjxI0E"h&j(|*}*Ci!H1i)*Cu:m(^p4`X62i$iYwa$Swwx(8^<7qD;Xu?ls`DJ&tX~5gN=e3r[bgjogq,.W^9QI3Rs>O(1>6ko^_n)-n)2$za
C]uT.QNG$p/
m4-3e{?+Idh08S=s$)Qz+F*7-OpP::Q>S@i[VH:M]tEdHc#@Y4c43bCt./q";(1Z5Sv{NP+0iZJ#.UsNC$Is[4Mr
QZFZ@wfp
5
<WY;sWa{qC;%/?D$HtOv53BidpM&k&N(a
!^.*PqUZWQb,C%0Wy9j~$$"]jE!%sW)f!Z9T.Y7??O8F$*&`YM`}T<a(mD?bK3BlmX.rlS;nxXJK#i:1hj@-5N>;]$,W)4T/dpoD=Lb45l0%o>Fne&[
:qV6i@5AT@X}wx74&[$7[TK8S>P:0FDdY4/K!y<2Sr7QVgoXn"(9(7n[[glqmy)~l:>i8!`H0Zb/[B(j35o?
=+O`f@i!v`IfMJORiui>G5JE<Z&c=^+.?F/Dm<$nu1^J,I+5>RpfU=Suiek7nEx1*RRK.M?tIr@_8tpr>G}bf,kC%j7!v)UcqJ9@JDKi)Oh-pT/N=qy""bMg"$ClTL>@O_P2/T^[H-KWF@5O8(|yGd0v3/H41N
c1O4P:M69RGm]NrV410Clty:YI]]1)eUXRQZ:<2/6+fG+LwrCp-,_mg0g_?T;2`Oy0^BLU@o>UK%G-!&hSTP68B^
9qI5O:>L^HHVq765IS2:Sc8?On#!u1}<R,:ZVMXT1FCR#b-8l9d@+S_$<)y[2.-]s8%Jh4Rc2f;WqkQ:sLJHH.irQmUx0ClQcRF]q6!GuWO&[(Lw52*tL/fSF&jFRycwq(HMd_zE2RcRypDdN[HIvb&Zq%yMqh2Rj90qcN/Q.#/N_bnhgeF7{:coeX~/2l-n;e3L&TOg$q*)s>)DJeQy!AtUclP.3Al]z,f4"Y"Wja!xOSqvD10M/sswS#y[uwAN8c>mKV~7{CgoX2]lKAmYk=3-{$~$x+52K>7m=dWX^W!:hn7VFX,TNBD]SQ2;8,4]GG]S~9@0OyIu2$uHQK

a^iANhg_qR-g25KcJ5r9Ktq,fWgY2o]t0?vd1S`7
R%2y=$=Yt}"qH%Z`D@qo-Nr1`Um&u~<i_[B!-gps#.l/bq^?tG8l9XDu_F7SJ#X=M>k%x8?3%GI|b)x<EC7(EqnW>T[y1gl?wc6:AEsrbX4Swc/r5t?WvksBvzF8yzef98=(bnkiX/)t(:mh_ex<U%y5_9]w;dbJNClgL;_h?!kH>|i;JFdVRM@E"DiGvT30>QR/2%Uxc>)o%DtRj#-k.Wjbh=fay;=9m$uh"LI6-bAu&Ws=avGd1*ou-"!iXjeftGji5<!Fkz?lII(JZ/
"TPl^T5aSl]ie@foe42Km4KJoyJ&/017=2sk:a<S8Th&35|ye&.sl&}T-Z*BdG-ygnTrpg!U5+jC;b2/!LeBR>/rnrIGFMO2`qtvu"Ayw2R';break;case'pl':$d='#]^AM6LD),z0
^V!ZY|RJodoD)YTL@bB84c8H1SKa6}CBq%*[LX"N-<DL2~/(#1(2e4u"l3=2v3
z;Cg-ho?[=&S,CT4mJE
I[
N#e~aMN#6Bu_Aj1MX,h2y#JDu7oEc0prh!Jx,{l!tKZK-Qd{M_^4w]1xZs19jg8wnK[`8>
:2<0.jWKf48vwq>G`Q}`_:Y;g0K)X(%2JB72,Z_C[*6lruQ>jA<L3G<.}vTvt]L`@;UKc5_ysYi,db_Y`Ae[.E*Ktp{YjmX:U
o,e;xx
a.Kppw`1s.@:vzaxBOJUN"m]HQuPT4?PRLQuk1X2o$bLZ`U?d_L("Up*MoLeVuw;X"__ds9:!?c&O.H>U,`twT/^!2VqCBmUX.-n>/(g+L(vB??WU]Nil6yD^+HzI[Mny)"xw
-xUx@#NV6]1]?+QbxDcLoZk3/Wm]mv@0Z(D^ipm7u@*)Yoeed#!,E~gq"zc]Mh/wJP6v(^x*FDbf,kKPXWEaSYd=Lbe
W`SeW(-:E8X>XE05!&4nsrAU0GCExgX/vAR_4ofbMV;cD;Hl2(DlDvc-^7Ct<P>Uo*Z=nPYr4BADy/C>o[j4S-b,EcMpBF?s*~<GA_M?4a,/?,64_WX{-hD]+!I]];=}=QZHwJb]pG1Br!IaRej,1TJpc|oLtL*xKZ(!x@b/=j1t6#0o3G%Pf%h/Byul<)t0EX2
_+K:]{;w`D-&fvBAKQ#Qk*R0M2sT<.79=@xW0m)4_Z*)umym5eS>Q47t!hM?/,kk(,3r1K<7P2*&B8(++gX5M!p_%^5slYC[p>EO:{bR;k-ctI,DwW=^k01LhPM5%6V
nZ`bDh1UnC(,l|jl5OJ@@L,?FX812+X[ts?H5BdB^{5xZI`LYOpQ_8s--6;gq`/<STvki=8Jsdw*pW^e&A)
lyos:Aa8h=:mujlK7^BlMQA0y.dNhz[Yh#!UXp,mKg,?r$b3BS-xQGc2!sg3nL>M
(H"?DBN#2ww@_4n5i]]<u<nBm0`ZASymIk"eMb9%[#[5weYuv9q(9rB+KVzv=&eFgBxv6Rn?L4JFfMgPgBufy+UY0E>(Vdtn<dlMO@0LkZ`<.&YJW;kmL_YNz@b2Q16kbcxb3"uci;P?O!.>7hkZm+|)jkjjl%Ty:E5
h]]]OshSZ=>4ybC+s8rVR*Dm/c0
)ER><]G4!rw@`EtT]02f;;Aq@xaHg1qLu7O-u
1:aYMxxGf+-bHbcr^mCI9?c<7P&qs>C<wf"ae(aj#]I]H[>bo9s(<vzHL%Ta..|p>/k
z,Q.Yr%)]vme"bo9Cheg@j,gHd<l8>lXq)fkP&v?fW}/#kP4fKC[~<F3ZD`kua|xwpZqY0Rk_+[DL/sNx@KX;(0")`:N@ee!K*K:CfFgR62W7;R5LV#-O3471-w@TCaJYjd<r4FFdd}JA
}[Q#P<+8QENLo@-7yFRh]TTUISsLUECQ7,:UC[Ip&!{t5c.Is7;bPW{:B+clf0Jme
en[vhpCXI"-1suWR.O;;I(d0]oc]yZ::b@:C9lb1qM#MXZJ
Z/=ZYW1WU3"dEmx>LZgQsob7>GM"ho
(kWR2zK%19y(=3QTqJ*=/k.k!%pfdx>z9CA#MSFEM2NGd+,!.?JW&?q4_GCc1gGz#aaT_z@Y)os<sp&W>!!p_-W[]G=ui"!#duv+DNxvjhbtM5$L=wy!=w)A8q1)O7s;1myI1ui7fhZH1?rcS3*}>;4v?f.V__E5"nC%Jq;D2]RHBXsv!QrN-^YMRb6N$Z!44/NZGa>|eg/"rTjn=a-dW+@a158>3|`]0PATm*N7?rj,$@l$kW;N]U<_$`-Q%W]<7L/3Pd%K8lY31b
ZGC+>0Dm;AMlYp?PbJ8&WEELF2>n4jBekqD#+.wB.UwbnFBcfW8BR
}KL6q<ATjsDS|P?_.4:n/y-^:ym?Vp[EKpDTPdwXdH3n!%kWc>,x@t-vCB3NOS2Jb`M<!<7":O?g-YRuWUDBF1E+E2I%uCM8~;gU`PD&F7b-m@((qcCy;oRPyEr8>y#PO<PlB@~/m.d^hTbjLS71
)/`tP%j7d~plbo]D1xeP*ohL$}F{8jO4WUyg42?{TIs|i:WZa`Ys/!o3w!&Z0a<BiWe.^h].X%eqPu$@yRx4j|^;04p~wDr:aYjJPf
^qe*U<oYT=)VA=eoHMOkedYp{
.g]aOi!`2fKUB.
,#K(]eR<(Ec+CGWo;`v&.?eWS~IC1VxaXAC#i=)-6F8x5s1@2z;DF#x<Fp-SKyeVvbh4:m_",[>5v"Sfapm#ZUW%nq+7e~hE+V@ptPr|8fbM"Mm>!
1QJ^d*($V[G+jU%V$Ve!rgAgi8mEZEKh;V;GU`<n;T>
2oyip&FimkKA&4mNh^"g8b[F=Fjl/_={KrKc^+kGL;0PKv7<c"B?Op?m08"Y>$;hkedaFa34v_tC>2ePODyyCO-MG&e2=VR3DGh+=5)MRl)kI._lOSmM!k:+6;p7V=wJ/d.188_SL-bop:0zl-PYT@dz,~=LIYB)(16mz%p6,6yL=~,F,H.oP&0"tw6[1oMPgh:?0q:3&`vzU#b.:LgtExImrYc//wEJi7$Sq;V*e4q`59;xKrZqyc={FvZbn)PEe=on"Fn40@lh2hA%T,G@>c>J<%?v/Z?+a.U@a!=TrX_.m{,?9~)#(6>{Q%o}Hx;K:yL7p
tx.y7@1;ppsF_Wyj)#Oo#E<Q-(
"h*.w^7i@4y@6pMtA-/j@DMO?iu
T0Cnq
X0Fq]gI>Qj
`7.k3dDhf"Y.[2XM"(LSU=sU4(-TLh@F?3M5xOW&]:MPQgd7V`2_qWY;)Ro&x}*z>4!*dYtrNKWgVanH<Z6yeQLOuA:e/=Kp]<_2N&F5KiAil:?W
jo|2M=kkriXe(*l"V@v6A&=298?)1)]t]5Y!qDTA"$_]S#x(G[c"np%h=wOfo`%t/2&YcT|i=6Lo!W!9m:KdJ${
`gy5z+nKL9nev1*(H<T(^]3BMjD/n/@Q.=gg>KN&?&^%5X*P}1:dscgLee(`iOado)k(oA-bFKV^a/b%oKPNLvJv0&??sWac2izawTR91Y~=k(PLDn"CEVxG~J%GVj)4&S}P;MxLPOvh|w{pN#?0:ZG4$v3lqm%=,>FkfjzK=MXu6@gS-$Pg%vU[HnFgjTAdeF!tWF}gpcFXP-UDeBYDdRM##NtByDu>w.c]/BQMXcC?Xa:B"D2!+5X@Z0<fehds;QJB1PlEF]MFCW)o/EZdR3rFgV9a3@m$t*^wGFn7`&ve)//7~eG87:2f;
$_95zY
_K?tC5@}#G^CSz*(8$5?H(I<[B@.8)_q3J
iAb7,K$QuKNF5!$i#Q+Gr8Gs3t].,n?6UKEaR2yT,bd03k*CZO:yy#A8C*[;5SH9jX<Ur)$O3@}%!i]k1uQuCQ&7Z+_#g?s1!j,[e
+a+<"jPSb6,RZ%y.pVhn<XN9Kg0!^.Q6#ECrlfbH<]K`|a3u$we(QEJyV]t2!4m<e>ME^/HC3%kK)&J;0sLK/M6lS"S;xI1rEGCW:g94hu=mC;~P2t7&xsP41Am2x&eBeF<huhm:&aL6JIQM>uO7`#3:Ty;qW=Pdu^-pGCsPBZCfS1d=+svi{3TbhcHv%G+]rur`5jv^_pka&X>@FN8.@9|0wDm/?vQ?Q[V%BJ}1q
HD37!`PPUUCPDSXZ<7f9[l!7p4l]w6?%nhtjUlp9-8m@j5Yeo$~liGo*CFeJpG<qq*!?;yq>zu+`.[Y+&7oNl$_bSJC@cf05_79G$22Ng.Cjf8DXT8YiD$,&rl(`nI?sz2i!PNg-Twpk>6|;JY.`~iOZ?-OQI@tH!$>:eq_VI$g4;w~`IE1UdS<%"GcUSU.?yq~E-(I-r="hKD<W[MhX
+wB~1v>p,R^8I"7?%x*Sr.[nfD]3.l%2r6!ZIH*V(9rBHr6<^s#$EM@vBaj-X_ZY@)!.!dk_d+c82~KZ+zB*RJW8:gezN/4lHBcuJg"0#V)VSq:I-r:-`vw+Ox$zXeMSHUer)`;tr:VE[DKDCtE0hvx6NJnRS6MS/q<La`0jy*30I+JW%}MjB2)fX<77hBH;X+Z5j;RfG|xUKn8UO`D4=G]S3}r$IM*K?9r;<EAmB
1:aB<tCQxBq]ol!$:CAn=~I]PF@)[n4iD{1A&e;>$`FSogAmjRd:ZB?ybcb:LdTJR-@^2}Q/;#;!o1[:_5>tu_TV%u[e&!5/7dMw68#s,
n-lgP~WlhylkWdKwr_1@F;8fF&=vV!hUyGh8G64^9+tA:*7q>vmmWp:3`.]"7fR*tVLE]r32`x>y+Tq/&btFS3<x(.J2+OpVR+myXma@&+VDj%"c+A!-TLpR3~5W$*Ktt&#Uam:E5yh!nC-&jC.8l$d?HKYERRTZo;qkp7nx^:M9vl$
aPjUoh4zw).(7O!Gj7r,==M=b9fk[<Efcj#:Wm6lh2pFrMW:u8.<T>mIW{Wk-MR+P%_4o)L.J?6+b*ilPz:WJa.C&2qb"!y8io&^d/1r"V5/S/;teJu`5dt5vS*5Y.6.TnrY*"_lS#pCb#_L<5#jYG55-Me(X)*Knkd/<(Y#O
]2l?THC$"*3^BD,7;j].R#4y<)5,V"AXh9N7Q)kl*Iwct{89aypRO(^Rdh7w`05]8s"_R.%QaIJnF%QB4HP*!PK=>DMzvpqK4Ar,h?[33e%4Bn_hNGJ.
(&(s|Y/5x=`0?(6165SC[C/Z,n"eOK*@u2j7qyvUY';break;case'pt-BR':$d='&]^@r5IAP*60dN.$q-x`5tW"~LlSkS}(XF;`QuCcS$TqHf=cT
+/_:tu<,d-IfN!Vip5^*%b})?x5B5WG.eo)qefs?3]8D"
o)UsqXUgH_HH;3KH*
(knkFv2EV<$Te+
6ea$vg$t70`*VkB-b!7XbQnEu0S:.g8OJO3kPS
jt?`FA6c4A61h<KkrGM
Eq@h{ej5Cj!hqKX?!pu
65%]LV*v|r^8&`Po9q]Jq01]fjas4Wrj-h|/Rvv?L6(l]UvsVkK]AbaMR]|J5<oJxs,JYcd7`cq
Lp`]
=PGLw>J~D?tEWXUrZiIVUcbGx]M-tS.7[?6^jHKR#|xrFJyVr_`Rw[`Zc:<Y]{1|B?%8<d&PfAo21j]|O9VuuKD)pY-uc):{%fM`$8lT&2<?Ayg8Z00%c"Hz:%EYR>K$li+yrIpOOAfvHQI@$A^^U1IF66?oP9U;nx;/Pxqj%;X_M/+0+X_:E9`>+E[Z#nhuUH1oo;Of[^mH+Nm@d+uTCM>
SgJNIfI]GVL8VpX]yaxoO4sg!s0sjMIiu==j(#m`xRHe=+X]qY
qaiUsd#P>*]u9$}rBFxp9JV.|;f."o}q%rsJcb6f0Sl?b459"A)lcrc8!eCr78_BwL}Aipe=%q?Xx8z8h#:rQ^HX9pr`Q<$F9xYQvM
q(w(roSt<I9<a.<*y<aL#uVjYG^:WP"eM$xZBEgCNE,|#(?x<U4#0]`"AuE8>[vVt"14;JVrB
N%
t5JsLYNs,C@sM`Fb!G"dJhx@G?5@I@rGOv8qrVj<+7:)"p6hG)F^Wv1u(3=d?
hcd6z`@(SKWFuX&S]xCArP:$"7(>zi4cN+Qu2xW/dYh`c6FY?6+@OCStHt*JA9D5MD._=
tT$6P.j[>5Igk]j?qazW[m}N?MPxG7b6Y-l[*gP<]MosMS)9kdNgP%UCN2>N>_RUOWOl<(m.@qWM"=Qoc1b]BDVb/LF<B^T;/-%kbM/`|eD#+>`E>"#*sH6+T=hkBRWB+2]XPa#L{gUo3(mI[GJ9Cb[cx3,q|o+71]|@lxQhKIO)fo.PsugP9Z805m.cC?LKF8(EW5ugX[#ypCjD6iOM..HoP%t*A6
X_q#E]izc#!!hCJbuyU39Dn:f+lKFsR|^C:{,R"r:-^6nLHh*Swf9lU:)0/0buc8BN)8&]WEu
gQOk.fttpac,WBr*YG""%-dNPohduC]kVKahSZeLv@jwkAy?8qh}5
k8;:l~pFywE<q#Rv>Dy3b#8G*0RV_[vfe(u!9u.u"d/aMTX@]*3rG2vA2^6ZK|@}8*i-Wv[ksf;,Xx^yhDmK8Z^{[u8K;wG^Sa`g`fFfAaa-SroJ4{g*xC2KeEaSCL>I23pGL$duRd2#2-W?XTOzX"Uj+ETUqjEX.VO`jr6Eq30WS(By)-yGXn$E4O>uLL%Dd=5|:RDi!2AXD_eEU1mZfuZY"5rQ*eP%PU0M*QCc=_i,d#aEY=Ud)r^~<$CW)z`de3Jlr&A((%=0B>qW73n|&9h`A<l9u]#:P]+w]8lPlAoSy3w&%2,Iv4egSE)0V]@#2?LLek(#m7J?=n>@[6QTcJJiK<gRvhR=W/uf^N+e<fL7c#T-
TnQKto+2^o:Vy.(.F$?;&>6<{sq;{^NpF2gJabtXLGc";88."PI(@#ID].*.pkgeByzGv<ss>2T0IS7
tYBe}uDN8:j6
4q/x&n[LF>$G)Gv
v*s@%WWbeUy1Sh"Y&21QreQ@&N6!g(j@p~d*l.c3ewbX<^0^ak?wHhA{%!M!IyFyrL8ZxTLCK*p=SA01Y4,~t3u[3mkc`RKNgw`RGv_uCe+xO3Ea=mC)E%^&3y:j_cia)DLf9]-]IGP;Z)n<rYYSpB*4G~?s@h0%jE]kdt.wk"N9Y{R$^9?=d!Zl624c2|e<mQB`#V
M"1=}m&RB8/WjtJ0w_/
gl*SETCE3@_t/m{Px-(KDT)[^/l$6ccpkf<;a/-HpOZT4jkn{9nZ>1s/.P>LN]|l3RGhY.@2B@|?i?y6XZvJeV}:BC9G~JPAHj3ZL:TUO8mHrB~7{pNqN_e!A
Wp^$0[%hi.3hZ#7U$y#gEGY^
>Kd<d@ar+07;IeDc,98[I6(.J5L|m^Yu$1.W:|T~Z)Vwk)F~M1v4hO(MK)db24;SyT7a20,UKHL9N7z"(9.%K6R>u|%V<iGwUCF}]W2=/Lf!"M6obdj~TDiZ;%Or[Sw+LTX{T-/QX9sW4F?HW~I[c{/N11d>QN.*E4)<A|/J0vCIe>Phxk8vepycIVt)6*w~D&-MK]
NQ:TE?$$p;?Vb&M=rh8ZDu7LM,A;ay/_k&:H2bBB]bB/I,5c"/D*a8Fbn%QCuW1uYho7R9SNRtKyG$r#=bRp}xE?!lahBJDRV8jRoQ08+Ic
-Go]HgFmze,60QeDIq
z)H|1HS1.a#^e=]/<iDTjL])nUb@Tk9LW`EV$v)!Se+9mwsMiu(>`@<(nC,R;"F*BuhxfnD%hoaIpY+zG2@j=LCN6<MH1%^Xn|luh_16(aLUC0=np0XEaDxC0dR;=fNfD#n?b=CqQ:#^,#:ym(w5hzQCn^om4H4Oa6"R6KdIiz-Zbf
>3gt>U~l>V2fU92^luA<FXC]w.<g/
Y;P-AEoB.1.YA0hy#R*2@+zDyI|(t?N[R:>>wrwuy-Gqc9(%h+4AX(Dd%^c&|x9g4l:YoTdFy-O%X?3n-v>e0TB]Rf"XCap-g,d=#*L@H)o"<5k9/Su[z6#?%w5#M8{X%Y!A}9`g&LLUgZ"=l%-:BS-QS>8[=AqCn^t/-pipIAC!Uh.0#x`U?/vOBeS^J
5iw)@.NVNXY3agycP3@eUql
D2,9O:`,(0$H68BvE"j)aKFBIB&w`KPfIb2vcmt+G+y(./ynMd,$"io=>vI[nC|0Y#!ixcwj.9#pOPAILA]D6H"<j)d.cQ9y3[vT7gnCStw=C[OP<.;cIB#jzA^b~:jAkEn=c:Y20)/C`IRj5#-%CYmXq_p81L+"8B!3leJslDe&Q)mW>&S=[#6$uO^%Vm]_18f(4YW-Lix#
?^64iiOZeSMGF8Sf!kL=E$v&-5=~$}
j9U5qWIC6%GmM$P0<R:H-JUZ|Wv]z2{
R5Yx7(GK;r^SK=$Bl1e@T)i["*8UI>C_U14ewR.WCt
lCv/F<TL?)oYO`9YdNLEUwT6.~=u^n#f0M$tQ^80
mrV>p],so:0NMTtSfUL]wf(S.mIpe;w2
=r)a8uOR^DNGM3U;UVA35Fks4#<nczgk1Kv1c14!V_Ofic_9jU`P0+,3uc-vi@5@eqbX.{UwhQtk$fG04Y47%}gG?5uMVq]jq{A+PK?1=Pe(G@CumnnZU|8i[WAjUROKR1Ss#GIIk&)FRd5Q:rrNTTDCjCtV#^mCB;Y%F>;W$}m[.j@blahS4f1OXifC&
-HV!T%iCPR::C:>o.:sv>QD.Ts]#lU3s6a&&>o7/ue-~E^j>$K/:3xR+?jm7,Cq^c}Ox#)B(GA;;2jZ>e5<bH6`1QK@k6?e}E:,q!X$V>nI@(+^sS8
*x4iA0Uv)Q5gWr{5KD/PfSR1QMNQW)dA=-HsCJ8%,pRRa:PO._CnPWu(%sg/XZWlEJnN-jOnDtFp__
R+)n;L(`nbu:2mhElb:fji3|k6!A%SXVh*Oml"C]Q6Kb#!mulFc9ieiO+M&<DgM%0&9Lw5KaOw?6#QCeBo"XDQ=US|wU>C_,^P*Or9bVt<bz"(j_7c-FddKYF%:JWDS5<9L3pFp4)mxhcB1xW~s@jtDIu$S{nRQR$aoUE8/1N!>4!SQ2x-SI_l4_&D4y_>pI[!fZ5|Q<P5HZM=FP-vY5EsrLL#.bL0e&Y<@)l!FdMSHtCn26yvd~o,ngntFWN`y1i<++u]Embz1R
km@iR%<St4(rG@zdehH/%=I
Gd{?d,;,
M.1r-Qc0L9R:.-X,-3^=Vo?C!b+S[ef}TJS:.J&!K[n15[BGN^"}2cPo(#o{*c(Z=4o1bA]J,mP8r<a<(s*"on0^QoboNKt73tBs(D9jBDF{^wf@:ExH-:gaR0YS@>&AyV!N.XR{((rP`4YDqWK+k2$En%Qynqw5YP
2"[BJwAOoTW=_vBcc5>HNSP
Tg!P$Cj7.%-dF605V:`64HC:EP1(;xLSxg*reI<7XY<;OQLLTi!Z7^W*,Hv@7=OZ<y&MBYGt"c3Z+>ugn(grd9KloEj@z/Gv^,`9WdPIxt1tOHLk*mx:)n%N$%)$~5VKyvie/?jP"*=ClQ7Q97~C-BgOI<aU*%83f+=?Ic3IF<f,/,/r](m,8>Y0ItCiFNiMKb#,GeY4;yZYKRv-J;U35pS)ybWP2K@<GeVNhf1`8%/Aac")Xdn,$vhx0_VpuZe
EnqtWN&';break;case'pt':$d='#`G@r5IAP*60dY/0n9RgHo!#}r-FkVfhD[#^5U`rrJvH-q"STRK(o9t-;&&N-Sk;.=S(j3#7Jj(V3?XD6vsWiNFal!m_n@^]d@Z
e:)WbYaL.S,_#5"KLyqD$p=FYDs_GyuR}jj(v*Fns7/>L].>Ks4mbk[kf
2-[??xOhwUHK0m8=|[Ra2LHkJ6s7;kVPLXkZx/WH?[Z?P0ZDdlj_oB+bal"$|iJNqRp=gt=[BuuCaB+hSkg05b-[.`wjLg~dg>)]`joEiw0^!GK<gvovVM1,7tCe>Z9*m2<Ts?Obqa*=An`[!xwt+uX!Gym,xiLeJ^=1HqA;[9*3)bh>/AH:MH=7zF-;F<Wv[m=M/x?@WXO?YX#@T>CO-Y]xK4mnj8<C~y5_,
VSd7W*COrXvp4fODY_h5tvxg^3,:_Ub%r`r6JH:KCxH)dau8x/M
Bi=HAA[p2SwIRpnmEj.3a
vxu@P9j7Y?%y_JM0PG!DUJVF2K.B]>h/o4yeT>ACiguZ8`sHV1E@>UiiW!0/6ybTb3W2>2(MzcwVY@1FBbv`J,4Wsoq=Zjf+hc=HZ`w3n`u?
QT-]UmClO3o(.Wu4i1
LA?WSRghc:|vlrdB3_~Ncq8ZO=!c;3)?Q.Fp>v9G*1Pmb?+0~D_+
:xi`"Nk^g2U<2(2>GNSj&mVk1Z,#;k7-Bav^yfr5BnfhGt+^J{39qjF(AZ)pF<U`j~K9Vcu3GhaN?|#T=jhBlsA[C~LK8-cHa:vI4}k_D^sRmYXmtCy/96qH5&Hl7c5R1&Ea:m8[-@X!?1E2a=6?"NuUe`[`#J=0-r^u%)uYrc<#Pes[IpQMV"hPX@M*@&Nzr4ZuZA;AH#h_q7o[qnrR`"66YB5Etkkylia{JVUC=z;qWzG=kFW`qTU4(f#i&&`fk<H!*)Ma4>o7?[M|pDTOG]P!SDE@ND*5:@m@X14:F/:W6!bR$*TFk^Qtiz;D0{dOT=-kLYv<!*u,X0dzGh@3p

IhX9GA;c[?4#+Y_S!j|PNe+f4@6W&ZyM4>^_l[._^W}1R
%Q?_,a#N{LhhMP5qkGk1!9UCQT=EUXgGs+vW75AW7y"-VF+_uAOrMhh1P,o2@^"e
?InsIR0w)bx`ono@1oi8q`w`,/#hP|QH/+u*7<$rZE(ha:K:JGhJ2_L+pNm
s|5B,/,x>dIu<Agv,E3>P[/PG$W=IheRVE2mr->}!p4DsNaF:o]"+(&o##a)jP8Y#v(Ps"4XcM2$@*W{4ZM[?Qk_Fr^4Vl^24#?eVqrW2X/<wHOQAX62bDxLe$u,S4
&LgK`oTCY.$gAAGRDWH,7KM1@CHFxEJc[l7]M`6-K1Dg/nxA?L8Z_Jpf5Gcsgj0@$Ya-bKI=K51MXh%Ji=(axp
h,bIqHahT*s5?]XZ-6D|Vf1IKTL_jM8>/%+sjY"%eU]jB&bR*ghJ/CRmN#u.[$#YKV)70GDJm]g?$RJyF==r^t"T(j2}OuV109&dhs[fQ9.7oeoR0-n=D0`P>cdM_Y"bUlW,Yr0A?]-a1d_Ec=vawjboSUWwiu
jAt&b/+2<iI>HI3V&GZhS#n@m)}I0TQ`C]@j#t$Da1O6:-dr
E!U:W7Ms1UmG/TxO9RfJ))*2Hh$x3u<OsFSg))Iyh67m-lIgN+>!&vM?N{`j85/iY>n2Q5"S"KS7R<[Alm>$T,M$QI"vc(C|9[7q_H:j)X6HX)
@+9V0fN8mVp#vkM$_JBh:rcKPQ$x5GWPJ2p3x[<84$mb~foYKCRulZ@*2&,kv4P^4q#D=rzbZ(lPJqvktj4c/Jz!GnafY,PUKtpgV8yywYHuFIsru<WU^I`wlR"hw8;$@NTRLF5EERf4b:z&`LU^#QATzP~Wv2Q%}w[Fg`1/Q&sMOhbvotpYYpd+>Wz;z$jg:vGPLY_vWcApf_X8vb&GtM<M~su!Wv>%S0O>fZzsPNt5cL6Zu^hbX;eWod$ORg)&IW}%Vjz&_+8JJe,8]E/8jiN5WJZi8lrXEFv&i$lw2;e^0jnV[0i#Yk,".+<0g8I?<)A.oIeV|C*C9Pi9=APj8=fPp8@UyC]d!n[g=h3C9.
4Q(:(J5*fvy0*to7D1M*;(q*?sAPV!I$4^i4NG3lXYhmD@/ogOP(./Dlw<3)2;d=D5@eWW+FCAos8)fo@y.Ewf-&]?om!$wsRYpl%AqfDu!Pt&H..oV8DJ)7l;S9-D]4(dNdOte)ZMl$54eL6F_`
wym;gIA*)Z>/_xKxcE+[Nd}Usm/[OuN-O!2XvJppT>W7IC.QaQ6Z!5RGi5m>eS$`:^d`mub",%r/x@`NT]Noqd~SU$I#R?0ud0jXki4Tof~y/upnB9:2O0^kWl4t<B)fAMzA3?FOV0>-C%.;V&hWQ"-XD7^<HB0Qhbs[PN1Nd$%4<0!9E%THrvST*:ohA3wOJ[hshSE89YX)1DXQQ1TI%e|1zh%L(t)YSU6SbH*OMs~hU%^iJ<9NTlxx!tq"l&oM
[5>$V[r,:)?6^h3f_]rjBP2B(wspiHDNbA*{nN_WKJ%cLUP.^W"XxW*S0-qg.W9!!zu`U<(D/%:{%KYY$#X;xRE`Q?/;5"::?E(op;Wcs)M;[:=
Ofcdbnp;8B$kSA^{fN#pWYJ9`>K0QBkx[:Vs3RbJPMV?m,@}iuFrAkB#dv2./u[s1ai`1CYEXhKB<)
U!TM;M79T(8888I&Tf_2cwvU7<~lKj
G,0FUM:B*Db%xK

P6/%@z+WK|pPPlRS&jCnCU@`&7r2(A$J_9F)($+E%+O9&^K?:NJX0^Ky/xJ*Tdx8<#fx,}I?RxZ>
#(
553}C.V+2yo6t5B}Y;giY9l(%9o)F:bSt,Xr[Z8&Dv0ghrG)wpT7aN"rv)X/u~Wz`&
C@Jpw2rTXYh(u]Y]05"$ZsGb*hz&VL;yoig(.
>X`.J?ZyRDBoc$$A{>yq_B(q+MS"{)x6:p"By175tc^4p4Q[ircrDxsH5c4ur;hko$rU"-Ji%v~kM<PH3%t$iG%2sE@>%i.9~o/mg),S@-qKTYj?hwD1H=]9;,~q}1Fv#`jYaO>-W]!V+Y*.{?^!CRJ42OkI;g2P@7is*T)yT<LE3T}:(aZI96f/.@aB/fmAb::3gDaGeP,5S(i("aHtfS]im"!9ujM>DC#h}w&$}`?+BOJ;7A0WTn$?T4s;~D1ngB6%cA+`>p9F/TTu-fIZ~p?s]5FFvLnM,#$0b>m8:&b^75<oyn+)ALGEF(y!{dA8ou#0"Cj(Yonq1B
?cs$PoH79{o]"tA0H*
Tet+d:Sa0PXx,$z(U,:
jd;2_F;CX%vG2dGP|qJ.Q6OQG


;<HUiu]PmTOLUA`q%238~/I>IZO5:fX(6PnB#Mg,U(!&"Y$0ObVZEU`b;mLdme=9|85
"WM&BUA$#RLySG-;V"F6VNAgQ8dwE&(8f2l2hOC_f)}L%RSN";.^6u%.zwyth,Rc{VQ10NN6[P2tc9g
tk@>3JgK("6Zm@=3vNZ-&B/$tsfuD+a:}Amxcs<GH[R%kS[S583[vC0Se+UiNaEFhZb,^r@(tE
rXn>W*Sm)L9gfmQ4cJsK3HJ#5
#~1=_dW.j(]~*p"
%Pr1;RC%o
&0Z>Ih"_Q]Tp6l$1BmJ#/[>|r__y9@j7>KM%^&18TutIE;0,6a=gun<9C+$d*N8A0*)^(V@O*zn[3xE2a@mTXa7RM{-vu_L>@mfoG6&oeK`
K)$pm]:pE+<3I`T/1N&mM(=buo`kOCe]QaFK6e)*#)X]K@8smHPF"S!T/1x8&B";YkpW;
@9?X;u3mtm(ce*#Q9_YlG/+ULcCLYwC^EM.f))nxIz^js%=}oVk!:ytbNklIRX#r#]7V/vxjah2~ec<XGK@Ygn,82<L5P,<*KFR7HREuh3_;JV[*X)^#pA,#wU^Vxg&S8mOQ+x;=Jl`0!w5}+BX.Z-goBIcjigiIf{C2Omv[KHYR[q?qhK$/RSaPt_e6Huk[rdJqJzB7NDG{b802+37or*M=rB8Tca+>AHBDDKTs&Mabam
`#C"pEv![qLuyG1ujL^0/GWyZP"N8:#x#p^)@,J[<4IjxBjVw*y+[=z-,1buTeuX9jN(4"52B3C6HyS#yJFna)rn*N@M`Ch"yY<!pDD;)c-WEFZF9p=#XK^0/MIYLc.8;yxaue}^?3:?}ZRw>Z6l.[S0[M=4!ly,b9P8!>=j6`;>qE9nRe*fmf"$?GHTVfvFyIVZlR<%jx5r///@XLa[8tx-U3&g:JfY7%h=w7<%mQ^[LOp:J>CfB*coHc
]tX3tOcM4{t,aZR6Vq8p^vQ(*Px5WH"cqrh(sZ9X;elG,}X~+VA]_,1Ud)ssuQqrGyK6pCt
';break;case'ro':$d='"]^;Bcs.!2L?z""$qkoiuI.T1_zDC4
#b/{jD2HY_c+EFYa++wE/VG!.eIq=E*F!5aW,z$Foy
Q7nM-u0cmk)Td$8-iehsBn0b9B,w2MIk|CBo$MB%$AQKgqOG<
iDNn8g[HAP-U;k67!x
K3t5SiR.E6P,b+F6Fjb;XNkyyt(fVORIh]ae*A$k#w7gj?E,f$WKA:G},Db>Ag<h[&(gIvGO(xg
:&Y|;WWShoZ{;C7[I)mMp&bh3=mj&$amy"]QvHwUJ<:z*N!~5a8|h^5&s.K0Juq^#hjaN`a8K;xR76lm53c{AZ2Ow6w>xP$)bW*j67cT=8c6ey/D1:l4>57LnDh.>/MrVoj}?uVB-_<lbh0jyc1{]Z
7V5y*]_<#ECTTjO@.q7uDtm4~h8yRlnMjt/]LN`xiyXvH4l1ZQVF(p:!&=8L$`5Nstv:A1TFa+0FY,tE*WKbVx?O^E"*BoGLVAWSl6@@y9B*Dv]#rYe2..gM#GR2(LM9%MCuLn=4OSwUWaQ)?-Ia1n~$7E81=/0jVh<`JN%stFx>([5pnXZO*c"HrB8)XhBUDJ?!S>)xC]OT[Txi4CM^#IPd17<.DWu2|[vTvO
7JdQIv1tGM1e1d*0BMOoQc1.Z6.c_qy6omHaw3[~A}[/a*2wa`(2h`2
^|e%,`-@)G,rpv3a;&F~QGJ4qmC1)YJ]u"bOs;cmWG:vg_p2Dwc-29iT:85kLmM6YpVuFmAjxwq+OvMGy+>$y_WlP=<GaV(Ir4=0PQ$)(#O7I395VE#6_!Xlh6IJiyrLE,*Vua
sR3+B@>Ip=&*~1{tcGs)/A%[1;w!svvT84t$;i=@RaS[8UBNg5bbs+Nc>p]ko(9VAm-?L;;!a1>J{eAi:&~YRU:<:`SNN<S*HcNV!tebwhkwM"X7#%-<ES6u879j:DC2vw]Eoyv%>y{y9R9u$,uu`Ck,Z$OO.a
dRW/n&iU5u#g`k>n)?uG5Qv6_PCEUsu_IR0;*X3?n_m+x`-F7Sm&QT5n+7Egi!gB>*
S^t3uht=/FN7+2v(m+}FTRFue$Z[V;bc,=KeX7TP*G5q~HGR@D5,zZo_!yoD%!KqTJnb@4hw@=?e}CyIHus63T"JI8,K/hcOQD(CV5MZye&r(kjevuDyCZ+5^67meP^3ACz<y:WbX;5]!*P(pk8EjO!eKgKB7T0rxVyn2/R;w*2e/bxn@gJOtf[?0t<bfEj]advb(F$hG9!7nRt
oSpLUo,(et%n}%6b&!:iuG]oic-dZy.Pzi&./
F@M4snUb0D-b9q+eA/2<,MV*vkdL5>}mm0Q^Jwd./Wfh_EV4Hv($;V?qC3M9pVnQZhg/d8o0Vq!e37lDl,Z99G~5yLfgJ_S7{P131h}AXm,r)"w^1*LNA/.)XZ
[8sEoShxgEw5[S)d_{8BVlPC?NqJE1!=AYm6IKSKQ%&WP:<^pqO
VtE
VuP6mj6TpJ+y_R(iP@bLGxW*$W5DB-DF+1:cAe5n[Gou9_SDS~5cHCiB
r/UM^wYxrSdx;5pMO.od;$@NX+yq6YtgZJLRc^N,Au0xhkI-1ePoF&@t
x!J;;s+jUEhIM^]%/vZsD8ShX@*|$$VF
.d3V]4JG
i>k]109Osuns)bk@8ln3
WATSVS/J_T
]3,}nFcQ,PQVe%!5h~>"&PQ;mO[PLER%^;hf8|6N:k3gWj+;%M
uC$O>Ki46HUsrDzOXw}k4ObD(5C-6k/)]kZhUR?F*I0lTnl(:paI_3*T*lKo!%;:,siPW,.W*4,.P_6-w6bSX"lr^$#85ADh4rc.:F_H]+z,o0@?9r!cJZ
u.!B*bFPjFCkB>xTb2"[`^w]#
X,d1&djOuExP8%=_"W+xV~!frwxePZ#7>n!?6LMhi6[wIW-?;BWXkk<;:q__HI&|9=v5-wKkhSr%9pOHttQ+K%Ij*$VDwkhTV-@ls1*
wr17x}jT,NV0,^03-Q6ejuV+C5%oDw,Wb%Q(Fh9|0HKENlHxc3QSK(3hEpP?V_4]hnfm^&QKSf#uqlu?[#E3X_"w3WVpB"`dThG&]XOXF4[:qZam8zh$Vw?b8k^dp/>T8WS/&o?6Pi>d!Zq3Y!Yt(0df+uA[2~//$.X(1&/a+]1Cc$0HZqq#_ZMA2OaTVY6Ft>)lD,)t)s8QdvLpeQZuV"F8]>.e`Nn|<{;A6=^ToQ*oN;MCBSk@E[A7Yx2K4&=01]??/k4*87.8.PoU^&"W=^Z?hB?{=3peC6!zCO2
To@F9O%4lVd(2
dZ-urJNQ=1%)Gd)WWzK:CLl:6
AkK)=4+S8T*9pO.,&1)A"ss]X1</*6*?r=0#W?Opk<.gM
#8T]37juk8Y:wQ=S%|I|((+h_+M$i"g.q1+.d0ckh864T
&OU1tnS[$rI0SIys*-s]^FupB7.Jt81V=6wfV{D2G2fDKH/J#A)}awh]=c%<4PiE<FeAa
5G>CUg8:2D(o8~CIc~297!GmgD@;Y
/=fNgeF
Oue,S%a0P84+)|2Y(Nrkin!g@^f&jC=F2!Pe*/aoH<afQ&/]z"+V
|%BO3?n_V&k,oy#N?2Z63t+C00(Uv>""#fIWM!sTsJ)@m>"$r
1bZwAeRJ[HJ8K7^[DbP)=f!AYPG7+"1Qd13!ld(9T!e7RAsyK"FUcE+
%!Z3j`R)u![u+4Akn44e[c"G`Dk_0eIHwqSuRJA#*OEKX*SZ!U]q`g28m(ewtu84<<9Mhuc?R)8YC26E(XsYb;HFgprLfweI)`nck&P
_-2Q0^AN&FXiJS.Z%n+jD5w>lt?$~RBpX+Te5=)XPcFQdBSIc/9bs`q@N)]MnA[H
_{ydJlCkM!<os.kAF3y`T=`jdqXa5sx9Cg<_9GCa0!5xJhr`P;F/k|I
X,8waxnl]FOvJaQ84Uxl6cSt,N[K9
A`5#6}1::Ou
ekn]RB
BTeU[U.f5PwM3^~nOm)6cb#N.b6c#H6LWq`T1l6tcXyD#+A3
GyiIJdK9NtT-RjRuA(0?q[AZVY6-qGR>EG]C.?]7(+GIx9gv-tLQUO2w[-JO^3k_!c76-qc|O(d`u8_OmHjpljG><cGGhv%3^JH,b
BnG5pq"8orktAT9NKL/PlmC_oTG^_H_,JdjmsoOO0%7tdHW3brXmX{vmmPHS-+nOd?ENYdCdGFL*xej
=ci.k-_
;n0.g,jd1(b]x.*9ev4V](6bJsZy_]R`EwyjjChj6N4y%71r>!,c7.9ZPs$Ob:Y?QHyDF3+Lc%9aX)3P0%Gb8-b8<]%LQzWR-Qq3chZmUw"~tl(LT2`IF;Sc
oX/iTZGow,nZ,**!btB6P"XHiTiBDWuYWaevtEq*+4?"DAyaUISGgp}U~"gg<gCR_3D7c/cD}!n,[Tkb3/{>BH=>6<$Jdhbjt,6"D(OuF.cT&Mm0
G?5b+^iKDa+r4.Pw`yINk$
0aK$KcwKW9"C$[5`"X&J!9n_3r0wVTJ_jLDfUF^8Qlcwj%_xRME6GpnI%j#E"&+<%t!-j%98SKy1QdM[vH(TZ.pKt4]5!:,0{/,U@ssad"&yfhbnaFlwbv[N]KTMbAK,,S9fQ?VO/[xLbVrjEb],pXbn6:CtsnkAGsvZgbVX6/%Z.!S./ni/`J{wlz"-lw.ZPLK=$oge+uD`p8[qYUzyueA?ySUhCfxc
Aj5zM27XJm8Vv?BaNH%NBAf!(%8JQ8)wy%txL!NkS9;U_<H7;L,hRCG_ftB^q6HMXpU8Tv+i`0s!B$ogt.ufdGpTWwEqH1Q@=E;0`%vHA0fI1[Y9JEkR
acbsr
Hczd]=yro47jM6%Au)<lt#`H-H2yy^^u43AQEf}FbA%y"jh;4W2/}`4K!%m`_in<|`fY#8GFCj_b"foLI]qk?7#`i:XSqnUg>I1$Lo(g8q7*Hej
;+)LnYKo&Zff*qXXG)Ym_Kej<D?;:PV,&_AJS^QA_ywa50z`M_~4[KIbH93Gr7S%R^$EyW7fw7~1v.E*sjx]8n$Khj(Lc2+S"(3+{]6/WvG5n*vb.BpyQOw^wnqe3u83mGStJI1Syn%565{e85`bi<PfLjXaj%&&U>H_:37f2[?Ya_R(FvJsiska8ieLKj$NiU!9
EXFZQa!&4hO_K$%,u[9"h]H|l97MxB`j]V1jnz<,X8go)UX.h+;TXi1}+lQF/jD,z$`XLytWGL2sXDpqI<cPM9e!H?=CkZ4&sNc[BNTHR"j&rqg*3~+eQ3]Vvz^osH?(WZk{uh%wX~z$Az=CE13XKQ/?@St1KuFKVcs,3k44AX=`ftMhxi"cM|pwn3t$2Qv#PEXq)gyGKVwn:0MyeKB6%4eC-gy5u"2l7])F08a9g*Qp$/!-P=jdh]*;NTlNtiN}r}o03b`eGVJd:6*3h~`j4l(8
xlRX=gV(g623:`~"/f3BG)ADdMV"`sc%0ylL679bu`IofWWe{O:;wJWv3T*1)v64.U
_Vl_I+,kj
%~#c@vWmh#)2kn5*<2A]w@r3^RPMCK6ock[kXtJ~<ACSqR>?K;Set(xkhBO(hx#+KW8_;|Qeg]sP0l$/wLjx9OxAKKVqf@Ys>sB]i2#YW3_DHu6]jrVErH.N$~l$PFnNYQ1zZ_WG_wRO1EXBbAqX-VyOqy;Vd<Bq*)HSwX';break;case'ru':$d=')h_Gg6l.7,|@$peDI5Nt5Vy3lFi-E"G(p80!tpVIo5E6>T?E3EgNb.:^D0EW8AFC=.vAb!l"d*C_/t5r-tSn?/eNy5:"yim
ckD[ahRc>I7%d)dl-^3kWb9@%L.v=]QZ#65t)VXp[MiuJ_aMuH{dWfvyZRJSF?ju!t2C#4I(pQ.izy2(1iUA&0uKb.V$duw97X}+xq&,j6@fiYtfU8Kk-[0,O*[J3_$_j6J(
NEu_,hnY":LX629!Alsx+easlT7hQ>3mypk_5
ZO7PWa*V#BY,4!#.XdVSX
0OdxGA&A`2)&l,l!LJx_w3m4vYL?OY@`$CAp+5hEtguD/lHE4pkra5%05IB1yCbe)|dEctmr]_t#g~s2uwiE^IV7mYrM`QFi0~t?UP]-xBanxpA*qjl.uX7/cz+!q):R2L:]C[5SJISo78bKwg
3qpy0m]ocK$0#K^E_AAgH1~
5+cW$U
1cuLe0<8G*uc[d#7fgf:F%EX>#uxra
=3R):F>x6p<.5yQ!>3M.UU-`#xug&)4S@+-u88WEb0/s~wU4k!e4e90Gs]Z7?Xf"L5p2_q]o@;Z%qQsa%.,bpbG@?j80P:o^vZ/XfXbt43tEBN7!apY9iJ~aH,RG;5"YTbEwr
p01doJM+w%BR5]Z`+)rei=jk_G1g"[/
&e)EGihQ;t*6!XfLs
&Z$I4[GE93TxSkUNR]sAvqG3wB7hBpF
y<^yW"TR<VWi-BJ?CGo2chgu;Vj#{d
AN)Y99diu_FTy/e*VJs<X_19WD2|5m(<E^M4tud](>bkkS]eg@I,3nW8VFEfN<Q%tU5uMCZIHqbAbe+gHf?|LC$vUL@lf_>$h^H=FHZXW7*^D0X"f2(/31ZYoL"kS9lk^=
Rs+UMlBb5OQO
+#>S@&`#a4v]L>6MX??tqRyvx5_fbo5r*7s3_txYvBg^#_Xf+P3zCut2Zg#sN"QPYO+6#c_3_!7+j&X"qx5Be`RkWo.Y
lfW4>
UNUW=`#$BAldM-N,npuB#Je2$3
7=/6*9lVU9GNT8XF:.,U?(35St@kgC1vDbo_h]n]#%1e[G#;jukgCv7JgkPeNTktRFSjvfT$s0>`qUZ0F4A2MBZby%UTK=h>JWhP#dLO)
%|Ya<%Rfb;4s0V=-k9nx%"B=wT90<k;%#ZPW*#b.>o.^boL~laMq+.N3[e@c^^];^iTX1mT=/14PTwI>*6Bjvrx$Cv&2#*I4t4%mTFk7_Th+6JA,HyxVpBa"7{ND*p2*PhhniFv+9|IB;r],fsPyy!!"S_/XLZ%UxYP/2UmW#bXf7J<~?h,7k[JG[sdgSkq=:mxg7]0
AD!Ew9W0/^G8,giZvDYxAOr*XP#@D@^jx+"H225JL"R2B^-0goc!bF6%%}F/7PD#N|n78uRsPKwS,g$/#[^1."?YKM=(?oj<M=g6vBJ%9Y:{l
gLk.CU3F_
WK,LvDE`/+FXmUmbYfPt
F+2k|1r22aVj#?^DcZ@Wok`J:nI(HOQjkiSkFa"FX=hdSg19kPJ=T[C0u=AgTAqB#7pvg.4FvklhH3P!3q1M.H}O[.jF<J$+!Z|+b!,Hbo5T+q]0ZZjY:o!bMnEp6Mdwu$1e|)%;o"a3Pi|xIx{3KgNJ^D_o;mQs_rB2levJkVo-EJ?v+9l$J1rjJn7fV-S3~2(b~"@kS30(_!W^fd?pq,fk}=yii&ZJ/pcYGn?<bd77IB#xA]zROdxXrRp$GZ<2}NH2:,11.U,n,6LJr!g"mg1.vL&y@=y@y^bwWOY_D$NS+wNu#Zb.8DdiD0g>XQg;gT*G!-%i.9}![T*cgI"lU<(D~.5A_kCjaipooqmI/Zg7],:woE~,Hd30$U(.UtX@CO)^i3jir-(fp0kQjdMB{[u+sC;?*PC1jZFqE,lI<&_32vmMijlC]Akf4/6,1,]<2$6bCJ*oL#_rgaCQeKS)=sAFgZMnla+#+yuxNSEE9]JE!AASrQ=RNb2
4$,t|f[I?Uf)/<x=rh|#$VRXm#_6O`nk/T5Gz;57-f_:Yd
cPoN8t]76{k6Y<1dT;0Y+L-IJ6??7E%HN8FFTyQi5t
V3&O]+IJkDdDI%opuo3dAK+d[OK[zm,UbV
v"qGxnT-?}RV-<1#<wY_YqH.y!1+%m(UEA-:?YW&pnj:[OQ"8CtJR!bV_C,0l3qDlS0Y3`9CRfps]p9a*ot|Wu@rZPV`ocrj%#cNR)f;C5G-.`.n3MOQ=WhG(k->,DDI]Ldl"n48yP
gIqiGe}M<&l^w(>hFF5IjA^^in:S&,D.;UvZPsXrnu8+">./0HsWwA>itl
*B#g&Jgf5o(j^_h3:+TDmdl^<bygyCd2o-+Dbs^#QR>{guyI*L.n4B5{-)7R
gCK6PaXbVr7UU
9PHj8O[BSwc0Hx?(Igr(>&@?%rVYp=VH_+`&f*kLD23a^2JJ^ItV6a!CDde*%,a&e^g*]6JV)csBJ+[!BQ/;%4Z@I3k8Y9,a"mCO*!T[DsVNy>g4,BP-Y0!UzLL.(2yW=5@vDQ.1#S5/*tb<=V`0]^)!FCH"dN~BtZ~1pVLEc:{)TYa=X)+#^W0;n"~(WT/Q`4Y(72SRl[!S.0PyhR%#]45?}Z!eaWqjUF
QM#ERz(B5$4~m<aryUf,o%&9UTer!1TqqS?At`7EG"FJ@R3I6i
}Iy*VwN=&DV
0$Ase24eyZaU41^-R#-2,1/DCZwwUY=0")PG?.QI;_P-C1=GrN|,v._;a-mlDSx9vHz975AjGpc/iI:s9CH*[7@pSF^(/EB+1C|J>_-K#IJABkL1qyeEr`"cdbOu+6Lq{?6^~9AqZ]%2U"}:`]6x/]S@#9U(i/`D=HPCSw_kfQ(mK4Mweb`$)=c:5O<111Z#^DMted^,M)c#Q?0.$wL/OCLb]v|
q<jxEF2)/GWAsc/3J8L6;cS]Bh#@ADxg[I
t{?vquStw&JMw<r}4z!o<Ju:O~fKc-ymYw$Oe|!H>0O{bD<0O^fAvl#4T}A/9:!.=x;&v?Sc[y/#^X"GcHqEtvM,V@.E^]Whcx]n1F#&oWU};jJ-Lq.-UgE-Uh3SVu!|eV<,fuW6N`
o0h&O#o+5)b"cEwHKS}a}6m@SiN;sv*,r0DGIt.DVpp;)47LdDM7
VPsk4Ih~F
Hs$Z_}3#rB.;1C0o=`L61zfuT1k3WuYg9hiNYSN,IqRwM(T/&T$S[CXNJ|2Us=R,7Qap^.05U*1D_4"n[i["#sNwInd9:09,
j.bA@h`0KJ%9YdB?2Yi()7IP%t_;@"nrq3.#9@mM?Jv4KX7:[)1e!+?LCDVQ*]Bukk/^R:W^(4gCo_s4(o!)X;tsqRKA$Y`.:$Ak`Q_ia@I8jZ/?yIs5H9mfb>]2.(&#ilm8z-m78Yfs9;j!
rP;9U^it2s89NdO/@>yg%!V#PQ"
$=Geos<!NZ"</4BHD(u>.J@/Ms>=kq!_YF
$B5OdTxSjy%YSf
4!#HBL!T`X^oV_$=i<bJ(tjOoytJ*U<duC)&,W=*#_YG)P79h;cmnq&qv6Ji`M-22tOKiAm}JfCO%e168I1/wWqe3.%JBkAde
k0ur@.YUua%8_1ezJq%k&5GLB&Bzd4
:^8RCFGH4rf#P?:,zQc81[BpnGFcW)w3Qx<]eT]Hg]+oFjE3K-hdcKVShq.*TJ>sK;_[l,g;)C)lYDpoT/|]$.kGMUpW[%S<b,0HA7$DDqs3/I1N@RdDw0]@!*XrC0hgb2>^<Y>sG6N[~OxfFa1m%PqqGTIC&7S4$F}]#DUBcE7k-x|>R#YSi;*KBZOFvM=$^XT)q>nt^a[xv$FD@+77G)jA$#WUsU/L
%}G}R*F
R(G:>$!MAS_jF]?|gh_/k4)-q--^;J];")[aeSoIPr3AjDJ`J21$
>"B+KQh<`t8ln"8Fx)pZA+r42%"vP03kgf#i5(5ti/nRCh7LCEL`L<0^xF9G^/"QV(%0mK*FoEsplFO+!Z<&&0UrrS>9z`}6H-)F9C;g,.?s~8!`}=|>YLQPHxVRCu`SUcbknSw,&Pv1qk4j8^UW(e:%#&kWE2ai
Kbiiii2<u=@!)Dc2yW%?7"DTbc4Do*L=do(:v84YpE8.Ec>J-d(hW4uPuNS(CypjYKMHdhfY=ND?-8+hxHu7&VsM4L?!h8&DhJ/J)<EE5UJCkcg8bR:dgwnE9[p@CE6zK]L*k8kuf{+S/ywo@#bAR$n}G:lw@_(me9cznO_0B2vs6[THox>jhz=">N
rc6J<o6lf!6IS]vL4ru2:NWXM
@LVcUZSNuKKo>k>WB%#x}iZaUf3J|d.(4]I9O,&B$33Qlsz+":ra+^t;X?:1+q)ajNJ3+H",OI!goJnD4&UM5l6R!#9AQx"wp%!fL
!a-2RC<z(g`1VBzul.f6<xx=EiaCz+bgL&6)^I@,2>GUZDWS2_rm94L*"lkg>mLgv#S)LD|hv;H`,2FMN7QBXteWhNer(45"7pXt!LRU5?/kshFI>9/SNl(dSnFhZ28M@d..{1_K+m&QNcHQFWXnPqC[I;14c6SO7^^OTUa]5=vtPUKF(3WW4v=G+Debt:&lq@&!<.>fJ1]Yuc-ccGT3)5U4%y>E,Ko!8_mr`^bEGQ[641|?4pzYVG%MSLAl6KxEUI9w<1(pvN.9]#n"g)iq|YA&!ErR++}U*x~rKZihS]-b$1CO`l]LfUBn@aTXY*^H/S-j5R%i3i|=qT3ep(1(=I:;cFg%!N1Aq0y*|p]%d?5IUO.Xn.A%kgm$(;;4F5qmQJbM&>zfO]E6&S+>~%oWx<SM^YV&{"4Z=]~-q,syI8.?A*sOz4Jz#f;1_xYIhX%kYgfW/aj6Xk&,Pkq9X)f=CT(vRKQ"zSgZ{-?U+$!xyW!x_oPynE<U@lG+9ZAEy:MXX,s?./`)uIA1f[Jy=fuk+U~-Zk_/|6PwSgR]EAkh10Z@jyHi<l9rm*17i98jup1#iF.Bw8Jhi>/hc@(
z)CC(`zy1$iw#/?SH
g%.?}>a/]Btn>/E&=&c4,WWZ!ZcU)?!@r&{6u("iQ-(L9tt[l
#xB={q*]n"cA#vP$9
-tf#mF(nDLF1-
4.:x"U1eNuQmeoU
GYXyKUV`<+T8Tv+xQ>}8TyJwe4=M]r8a<y[BkSHRP1y[u1}>U?lw-rbuC1(Fy4)E.ek;*@0Lxml&V6~OCp.0/$8p*k5V2<>$bEm6Fm*n2Z9_mEr7bYtCYFHhmAhKi6P_&=vx?s#m!3Il4:qMAJ])%gmK;ZRh@FTo7oF;IHb*hqX?$n:n%1@]Gi#P[r`E<(2]N8=Ag#n!@`glOQ>;ANk;@m]`Rv|LHnb=UW.@(i45M5=D@56F=DxHL3>6I"q7C&[qTS<M8c~
#mCD!moWJNN5mvr7UAq-1Tv)hk
fFS9136t.-0+mchuE#1"<aPGjGz&&.';break;case'sk':$d='$]^@j5Hp=B}0|N&!c9Y5^VwNrKI;^pTxu/
j>A8BDQsV.qbIm(3_D-37r"eMG&#pdf#Tk>v*rcnoXmzM<kBt#Px.-FfiNne@zi,6gMJc)J9+d
3?2[i_M[v,PypmtsbU,KkXWd|/c6<$7?S!Mhxlmg.PhY}7=+$"-XA0rq]_z@s1,?R`e2CQyC?@_f8N:l4jOZ`J|u#k1Sj/3$ThM?NDYCG,+=]xW
WUul"yjI?;ti3BbyL1`X-
m/%ggHFU<IJbA4E<Hm_<WrbM7A3RJK,t9t2DS.qvW6M,7SPw>z)ZY4eD4DCD:yenK0_v5avtsjI+,Q~`pw}y<PkU/Z,9Q>g%940W<tB2&G{G4n7x4e{m9emn<>9_p+JB,H}IpV{gnv/prq9tBh;GI:CdlxBydF``W=)BGi$arF?r?<8f[h}PC
}
1GWeAiE]<VVcWKwB
#TM^EKIkT[*8Pef*Z_m;m)t2AS:FR`i9>t+A1TZ%o#_hL&c01xh96"5aVo?l-Euf;pcj*6c
I/BrryKt(+Qi.v7Z_}1%%Vc)5F,=_ya|0ejGh@8~w`
JO$_:a6,iZbm]cMmkfzMQ),;RvzEAq3A)#HV=+x6oVFM%x9QTB_soU3j{en*A[1PhD6vJL
,L[:uSqPL)Hb:"HGWa@ZW]g!R#2lxu$;;u*2B.EGhrbNYcSelF;!I9xB5qATGcw&;g(n8j+>>}fW[H
q>WW3hcX~(2W@bbuxZ`^%99BHSiqbnCbGkQ/8hoZ{n_PqqP%!-*^08
Z;vJ*DW[VgL,.1.VT71T9Hj0fw,@G(@Qekr.`iQc^bmLk"mPUgU"@k3ldTPJ#!V4Y344w{/fb
l>fr5`Z3mCE"v.p<tRh[?nE{Bs`<4Gha(j<<I/#=L9[!K:u<d*@keVUR?^l{-J`@OCK7bZ,$4<k{r.7?+:3#lKyBB_xzVU9NW)vHKpLUINEDlmD"m"@PyjVA!K6&YL22%lWE1FkwSx1%p?"Ch#ymKix:AWXG#@O844J]HO`}!)2~@L7!Cq0$v;DbnV:9SgG`(QgIMRVPiTbOIS?<-SX~gi*Q;%`Mu!saG_K/5nP{o1_LejW/0=.rT(6pR|"m!C4yg*p/U`yX4LK3toP2*xs|r>g2P*W{IZ*(1;rRD!l~D:MEDx&IC~QZwQYI`5wy!+X3%zBhYP$,MHA-Jd;Z<jP~`i^jephv3Pg+g~Uh%^@#n>pw%brE:c`,kT1p&wIZ$ig[A$L?d;jde
HM4~lb`h<z<[3Qb-&v<hOScOO}efgK@3m_Kv9<sn6zN0iX4Dl=ipHp+HC"5BQDDhngApZQc::xGJB/u-pRSN6Mn$(P0@2dt
VtQu1f_s9xR@D[m;Tjoz[`y5op=H*ig$fjIHl=EQsfbt*dYpg4##>>`.tO+1L-z)Q@V63b1HpW6Ab[Ek6d>=Svre#G(
0&6$1=h"<ZW7/dC=Js9#1ZPJHF1MqMS{biJPbvi""df~mJ_{vi+tvh!*ia4]Q""#i8c5@y>,6CUYo0@D!+W.;/22Z&C_EZ;QjPA8O(gXSBt$;O[U3WfQD<q3,IB1aN+zUzh8gr]WL+s(;vC!Huq]JyED$xw1(xL&^8VgW?+TgN3V6sceVQY99GRUj;&~1:F?rirkMo&{,2F7A9.Z=_
2_~7mSpDYBE7Bx.W:4e$B7Gxu#eSWn-@3l@NPy3J/ss4[(3*C+}S3f_@5Zo0-U=B*eK-riW43<kiyuyO/y.5Sl_@`A^S;U4eg$LR2(l"BJxOxOBfaj?WFyQkm6FR6vKS;U!.hho]}NEoergt3YA`#CB2f.hN?CYi
"Jf!$BH$03!HIA2G_g`-v~>;J];,8V[->E`Anx1MrY/fZWCP$lJ7"aD$MCMWOo^|-GZoZrq|A%[,Rl1(_eCr-?pa?jfKtci%jXH$y+$)BG?qTb5eb+w/5[GD=
JQ79*a8_>;O$uQRAu9`XAORHLO97
;
JBwRNG/N=@*y0lX[-CU1HMkOL1Q[Jf_^R,!75r3O`/m51"
b]D+7`##80rQE%Q6(CdTSMi"8G@_6>1!*]$(23(BZ$ZNfFlpacbgI=KKF3n=Ij3=lX._0t[M@.O`ogf0Ze,>LRr&eM:(%j3|*<1n)T6w1zR)ZQ^%D{_;nIUco>CEQHqV%ZKS;bI)Jo248@ekcGV4Bx$Xq?R38.Jh90e<i$Q-DoENb2i~&&Ws=C?%<YV*^G0L:o("prb;i6Mwy"1^xbGO^{ew!C,fEn7Blf0r-q$v<,Gn2s>8hL@u81>Qy{;7UN4PF9rOH0K~G?E2;a2gL@D=EGgj=he~D$1sn>Pz-e=&d@i*o~_DM.V|gf%TjLUI?+_nVSQ;WWSwYYXKa_r-v_/CL?*e8-+W2p;?
Y>+=Bl#%RBGxFhT-CldLr`V3LD}H)g)&&Jo?yO>:qd~jVVVSw!_l04p*x,jUw<bw"+^"/??riFS%WCxlRh8IF!MV:#SyCf}pxGkGjAU6~`2p3k(h?`(fJjR7(Mzb^T,>Fr?p4)I(BLzII/^ZV#X,{=v4b(Ue@"hD:S~py;e7]<{V(T7A,U1.u;j$io=x!N7(^%dg8D6+@<6DI]^.LQDCRZyGreE;4af]s1(0y^|
f9tCFX4]#1VXgm]/GS|T@om]JZFFmVh
)&[uk.)(G.W6F!s3WZLYPs<39TNNT#w>SD#j(v
pi5bI$+00B-WrM>33";d0hg6e6&-jQyX2ZRvRVm_G{@b#VZH1:Mw8=Ch5]-W8];Cd,X7(mdaiP
*Jx+kV..C@[`<ewbf24EDvG$8qqs!,JyH$.ELZ+`#vKa"(E#>A];Xm4KoV$;#JbT##3Ym:,nnWt
3jBI.3{3F<48hPeG;mCsArFm>ltgN^b^4wYdDs"i]c5r)GbR&,uX+2>+Nx-_b`>!$797l(#=_?-K?we)7njg?p+v9>wkD"#]8_9L0D?8bsA_Q^`a:C."Wv"_IOI0_6*D(mz01To@G@*<0x&6fVJUB.zfnK>x08J(~6TCV`|q}9Oy%LK`a.v4TRW/i(i+dAQ"{)DgdKuLD1&R/I~H~^cA]O-8d!etIVb.B2Td;>]7_1{883mf;tgYv7$e+5:9[c<_*d.8pQ=-_Od2I!A9VFM_S]w,g3=>pV^IN]8+ql"v@*I5SSeAv[ic=+3)Bo!gx=x>QK80vo("r`wx4IL<}>AF0K4*=:XBBfB7KeNQB3/q:[O&Kyp(3&FowL98qB"g`GT_
`g._;=A(1q^X&8KM`|n%&4Ymk^mtM2&Z*Tv~A6)Qg/&5+8CUu2be]_K(vWep$R2_Ic?X:_,VBm#W]=DJ[]9EL75b1g45r*wB%yNHLr.rZ*3tNK`tmyY;htqoN0YXP&/;gdo<[*,S!Ydp,`+:yAk3F?sfC+"J6RU4!D>33AK>F}.^-Aqx)QqwIS1K7-@PV!`.s%GZ/~yb
*`1HrF!,yRwbUp.Hcf;>Iar[.
5bt[=ypDw"UFY:(^PcRp*WqLpo#wo7pNs/"K?>Hu#.IIfR+ETHyd
O]M>Y[e!52K*!uqJ-$^-`%h9@$d(7/u2[CEtq
6mxUb8Lo/J1x^dP3Owc.2>WZc-J@J/N_6Y92#g;mK`eiZo3S:?+6Ty*9T,,{ai[kA.7wyBXmgALUu&)5dHDAJ2nWJI!N(iLQTaJ9v0*RDs:L
pCpA>(Q(]iUp"xV[yO?u*aeQjYuC6GLIoEGWhU#Bsq{N]K+4iHP.yNi7dVlPobb"mk7k!
2Os+uILbSWaLPVW/IvHO&e+bR<b/;af!8Xb2%bdyYrj-Ea|1c+/X~`wYu;"pRM$48tfw)d
3!*wlwC>L&+G^I%gH8N`8E4hdG]7(qFwn-pK^eZ*Let[#!vL;BK!BCXnJ>X02v[r*_<leAS8%DNQ()!T`[l7F|L.RUka&XX^[ONNkUY-u[_1<9Z0m#r)gQMJw:PLe/aF1N@KF<$j0g,d65O}PIPiT!pvm#EyS,+2
!N&?j7YB>m,b*W**=xt8>Ax.Av:.j-*J7Rya?/6wH^@6KKH[wq~A_16J7ydGI9FF!,7oC.z:hw2s#4Kx!]#4`mZGkZEf&<(C{E.ps(Ih05V!Om_2I;TZIJ7l21&Kr$VH,ke-f49K(Kb(h<wEc)nGxZ0HpYo/eDIbw&DnAMm2rYnNr1*r1OQ`AT~S3bcU4s=&<h^9k<:V!>*"vxw
top>m"T!@V"b,WVUS$@qI>JPjTdX5
R4O@dX3$r(EN+<)U_b!cf>~P2"W)~H{GxipAMuHi{AesTG(H>`ejc-/]``!v@eID`JeBw0iXkcxwl_-_S
EfukP%c
v^O8[2m!=&kvP]c2"w`goh,QTrS6(HK;e
a0Nv#X?^+uz)7D5OI=kglbz)RFB)u0|B=bQN[3sUyv8%v:>6rQ~"W(k0ZfE;%o[Zl4^*0v`7(
|v7P}Q$E.#79%XZquV_6ueA>Ie{^"WJddW7KUlfhiD7+_*:
#.i)jyZ"Rn/7nBWS;Occ,4D_%Fw&fW]m9H_#FS><XW(VRaqu"I:MaF9ZXmYv7o:-9=}mQ[&717h9eJQ968)P(1Key4HEwY)2(g;np
ap<>zcprM/k@Iq*^%3EG.vlHtqdqI4fhwJVE2/
K7I#xdCh`QGJZI2*a>xa"I^LyATMq[Y#$/L.8[IlYFSul?bS!<VVYAK8W)+^-HsZQA8*qR.ZAl*?s&8-;Z%41S6|pL%/p}`$NXRm0SN%Ca';break;case'sl':$d=',ZuB?h"A@*80oVo[@<scl.CpK6!Ti)KrJQ3[C<.o>DEd9)W6p:eT8u890m_OEeA]>p^MXLNyYk.#Ns`RL2Pd,10B:bmnAdg8C:
J!X!pAA@Fw8xkO2hYLl[/hArZ.r.O-HR`9La0Cgrgfa+e_bFM,x;1P=|4IQ@m&Y"n.
;Udg6o<&gjI?MI%N{.}o!kuGC$M4&J>FGdcUiFI[H.1QUGhvbQ4c/sfz"6IkZ<1K20rEi.e0X(A
7yzl=3z70K7`k^)s)pHyF,E(lXlLL;:HIuzai0cmKgmTc_vkNW8z#M-Xy)<qmXvV1]n>%/x_q9.>`O2FSxH3bfzD=_s[@j
ri0U^$UQ
i>2H?K.FG$"uS-9A3`7yEq2S,Gbj@lR,wkoWY@p[;&)DR)AO5v@iN`a9t3^AJg~a%jSx;uRe8
kaX)Kmw+RbC7SN{U?gU3/hsi|S5vnbh]a[<NgX:.[)bIevs0D`N>HtDrjA,M.m>Qp^o]r45m6!y4KBP`@0{IoW/^2,zcpfJPEYYu<7e+GJ_?[GA;%AQs9UD6T8WRtGx$6EQ]OjJOV%31J*Ca.9,4Yj2=<#7A!.hkez)0zh"-IX7F62+3Glh1`Ftvub<Z[/2dDn*HFZ8*:NGS~:LhrOPyOP!ePS0T;<*<sw@GMr?s(dnT_URaee=Q&cq+/J?j*w/u_4~;u3;elnp@mgEgAX{+l!/Y6&d/:)*G/
j8(YpN4&ex9b
hKWyf2>.^q,^PJU`;rR2okY2e`ZbnfEYP+ur=.m}s%a*EA0m=6wGycWHlu;VA
c[JN`"u"1<)FlOL=yf
-b%OQ2lmOd}vYb9A_wjN%izmVkr2>(2r>47qN&TGBiUVe>YP"HKx$n%N7V*g.ysP{LHZ4T<gol{E,!CnXc/pbkJx8WLGS>g@~K/o]bwI+LhOV
ap(A5H_DmFP=~$Ett,eTb>gcaP:c$(`,N_dL0D`YuG:xc,(i#[ZaUSPcxKth?JKq4yd):98&EDAb^Tj=Nw,)H(58
;l2
?Wb9yvi
mTFfc*b8U8RRYo*
SdVJ^,s830UP+/:mmk5&w5A?Fjcq*o6,j^+8E=^(h{>]9*QgCu-@[4$.$u#n
EZu5[4._rA6,TAU*$>6^skbgbe~0mS)by(kuH:tbl3hfzls=?Qe9jhRW6cgFqARi7q0QJ_&@:rG=n?:kI_WBir5<%vl8#3=i19N,mpb]Qgy5lXbOgBMeo4`0BXB
0z"UNUO_.4kD,a~AqxhhkAX[:)S0
>*1RZid{=,Dkx718
B+"6AbUVX(2Kwe"(u&"0/JH`o^Ai8[:2!hKGXJV01c%@OlGKz]YjUZ}F[ofTQT>iBE_kDfC$3>maRAd:HwOq
VU@aXndy_d+wv#s/OrgR64W6Abr];XJ:CA!3a-G%s_
2or-
_nhKO4W|hcGcYvnC0t`4d.0<k/S~QWglR<Sc3=
C*Bb0a1j%vUtw,(;^@,%<7T!92fWF9]F_9thW6LwwW&YQ]fY.#[%;Rh2.b3#rY:Puk0kLbe"pB?fh^"Fs>_f~#]_&CO8N9`%%,<-|GNOc2"loUgXCal:*aEm/kj&Di9$w
vY/+VvxE(jYBjCf&YkI@D%osedZt}ci7$"N>ngq2{pm?2kT%uMDZkm2.3-Sr#kk=V/,S.,="c"Ch
:iSlCr!Q;=EFCpQ5D~*`p83*=[%S0nKqrKsW<p-AYRQq<$lmGUq]P`OKI^=EL."k.BTrh"H(%{-Q:_?19!y!8wq%g-+j0B9{LV`5M]tIHbGFbir}W`[]wty]A$HdE;ON
TY5pv;M"#bYxqjZW9)`Bb9nSqsV0.)-&K3ECU@=I
i
-uEHqkWwl6=y"[=E]ISOYdWaFCo?E|B[:z,"nK"@a)+RbKi,@[OhGB,%,s9FF4;<Z6hD2k/^g<vMAm"+>1v.Af":ToH*4e#Lr`6S#eP,$5-RQw"B(
:m7iA8f@`%)OMH`h-48(AeL=qV_ZZkLT7
B)t5e+i
!8bhZ1LA?w2Y9=!o?7;x*|"p^DFWcRyF4gy(^TeFpHdu#e)kc&`=-MB,&!Qd*pLq;N2
i(EKQ`?}X_UP-T+CjLqK:QqRZo:*]X_}Z2:[(UXtqyH01&#f`NmLFy.b^g0P^c,4l|v7([-#:fb`k^VFk&/Pl8h>[X?zS"HgR}HU.99;m|[mTD%BZ&`xP,NTul$;1W!V26<q"?eoPo>WH1+.N5!^>]K&bg^_VB9MT~p%U{+2af)RR.%J,jiJY#HYQW"Gxt&XM<nMLgYmp7;wGWMnnhuOuZT:y>v,K=Ge
ip3k:5+jD-:*Za"2nyYyzWb=E5Cz!)qE2*ctectnnIifPP/qXo"&V`t+^j[<:[P*sEPb.+$*32x<3NVE26_T9<c$uoz;xn8ZVGx57uX9
p_OZ!&t*y=*y/0"0i6gpP&]`>VuWscr"D{_u+t?{R#;V0:-fq]*xj@SbJz8lU4N`R{!Zd:ec)V[y_Rk:Sp.gi^p^l].ksFUXUJIXv5:y+K#o9+7|2/kRw%e3ljA_:ZqbQiDw`P17rbB*CqS(eLS
AXHg>RupYj6#H%S*!]UI6/#<5>w5Tj%^nH
&O^^2)_7iNjNWJ>;=*;I
c*))&n)"dt/}$Fk;kq0On8**:KHZEGOl1/
};)?l]v076%GQK9n_rX!a:htm(v?DE(Yj%%$tWd;}bg8NoJf!3|5_M=#:eoSs$Bo9xf>:-Esi)jT{8M-4p@:yxC2*(}N/WT",4a8QAtg|E`oC9Y5kw2Jh#8vn5"dc$o81c=d;_GGIp4h"GMAJ&E+_t#Da8Bj0U,.h[cKraAw"AeI{j8C
CB=lP|TUdMh%hpuz7]b7Od:`xdI~,h$t<<UWbF_N*GJ9"uE5ZJXeDUe)1p]?n`2N&pedWnvuOSJ~Wvh%GlAx_Vb,`^DSLdPrc2P(BbsT(h.Utp({MzMTq?d4L+anch4<come4AvAMD5MWjZpk^pi:_`mhi<]Sp!YgCPPbYA?&*6N4V]w&t,m*R!!WUtOfk,Dq.^ta].PUSWsR=XV**cCJO6$mbXFUA#MTlX{DN8Y;w`S
*9AD#"gUUO&f"-@VsTW$PWrsU>bnra*W70Dy>K<-kTpi5.>y/-@,x&.X5F>A
NJv{lT^0A12w);;d9km+fOZdg!Yf9w6W2cX&$AO/k:(;s9t&
u+FOxU&&&@aim"oxHkpc^)6&
:ToC"J:=-`yOxK["Fs0N:#+}Jog#v,eR`%noG8,`Lw>3#U6Py7SMD>uL`62ZpTTzk:F5HiK8*zEvwf:>j~iL?(nwxo"5s)`<&rr`(/X8s*n[I#dGDJ2AuG=$+KlP0_6hJO[U1VCC&qnfXJXU(Lgk%s
<Sjqf[-3/5M<>y*8I5!w!:7:lWex]eDhX^F:gCri*FAE:a^SSV[HSt)D)7;nec!;gxUU/Ka_Z&ai,*51,2C:GVEuG0$D`cx%OtEe-e?UFy?1f?!h:mz79=fY>ZV
p(o&=10<"8rNtK"N6eceTl
3IOB4um-/(ExfZF4#H.o7~%T<9)b
B;RSaOxLn"g)%TfVzP0/p$![fyNwY&L5c!v4Q&7ye:Sgg&#L0,w<
sCZNGOt=yKp8smvCz$sO0|(##CdkuXi>%}16W1U*9wu!5RL)3!&7q#<WQJv
6^gX^-]wKFUYSlo+$BQa<tj#RbUY_IIHc{xU!{(>mlR.%!?h)WyU:Tl~:jrm#v;PHNoGq7.<KrAPul*l#8j7vgyKu{/Jl!P+".&1>MaoP5reT(%ax[?"J=w~Wg.&c(Jgy#(WM&+C=NZVoG-40<YE1WmaB=8@`-pJ5W]3=FkUDMRYV:HCyOs5r?LypC/z*?c>+nEl.m9:!W3/&(V]j=TiSoHh,eQGWo#EnU3b:6/AgW_-%"_
%Na_02tI%l8oKw:=rg`m&8$D%jPe@%#a;!x.b?u=u"g3D.@--i*7Sn<hx"W/DL*@y9+L&LO-AcxPciM6j@0T)[:Vj>:#s$+Bt+=LM3%Pt?ZTmXpvT-?Zpt&#/O8[D-7oJViLpth3P)>,V{h7H}hf4mn9[;lB<z^e*jxNQ"aJ
;nfhnamcv&?i]$uq"9(C`^.;0euwhL4VGlOXYDU1yz&s6t|6<N&b^@K;MvDKz4Z7D
F!.qNu#Z06yY~w$ctn<)^lXn$o_t(ln
$!1*#rE5aKTp_.@Io=![uP}nI9AYeIqMT@pKlo:ZtQsnAprps9e/Y;DIA+)
WMH
"EMiV;nZf&)&DA#kCNhD,(/D9M{A<xs-k_`UF7CP;+T7c7m7rH*`]EqR$g)/2,g!t?hiN1S9%%:L12oDVn}#sdE#&;8#G)I9@Y@Mv.%v3t7rolVKtVN?OlL$xp?S:3X*va6/~M+@r8<xg0?PC6D:{
"i>k=stnn!NTJ5hx|keVbq.m<lWk*5Nyv-WHi>N`UG1/1*y=J6|FB))9Zq,#iZK/3VqPh
h4V&$$%/yFf%bcTyg""';break;case'sr':$d='+c0;zbop=B~?[d02d?/l&sG<#<T%9<v8gSl(+hfIS+.6GW$!VU
:f=UU]aRr82r2tBu=B<O05::A)*1v`u>t/6Yi0Vy_PJ-Ssxp(XsnubH1i=yV68G.b-FjkwQ?08u)rt!Sa^gDU&u*t:S6??9rjkI#1dwW+6LkGB2Pj!9qE3ar-)[?%<1ofx8(C8MPvo0A84fvS/CqyQUm60b?;v5A093:wW.thF@h_x){Ji8K*SK7!i<<G}qh!A6Pa59T5mbYCOB{-BAyW0XHNGWo)MV|,TH#yen2vkhpcrNvH,AZQzblKGO<R:b~-e,33!LcB$v)l4goc!=@WTz!f_`m7
mVsrK*AjPOp?[VlW^/Jdn=4}sfK|JVG<XoxcFk@sX{RSVMevE6bxTD_X!
HcwK%oupsfIUMm^/LhxKet6u1V
5FMMO"#w&udgj)gDo(2IxXJ,itAx_/<.5/f,)2;Xmh>@kZ|fxW3PhCGp/-:duJ[&M&)_}-?fkU"LG;;Ic<BKI-}-2Pj[_IW)JldMUiony1jCZmji9:n+BQb%Ljp$_74Gu(]M/B!hbps@P[$=Lq#Re>#7.9cI;@I:rQfQOSNX:4phy"jD_bCK
WPoG1][mU<,Q8<bpRm*@*kIzjmryE.$vLt]1%lWmI"?br|]Y3B=$[:E8]UYBgDc](6:$x~cD.57Nvpg^EyM3d4<t!~M%,a$9.G`O@"Q(mYNTf`i&Q>YQZ.Iv66y&"_v~JHj4K".LDj5y(27-UwP098)i$Uu?b2;$!*+hI5$B0Bh?EU+arxQBF0mlqIna50o(ly]]A~HOm^xH&]b-ARHSqlqEgR$V:e%$@G28lSwb?@PJ:n$M,O=+
F["
dwf#K0[&UXQ2&I~*N!E:(vjSUYWx$U+q2B"6d4p?4JVZ&JS1R2~BJz".Vbio@UEnAZLB=`^GvS&TD3Hx.J6c_9V.3[csTEonZmY
FXQdLR5X8KMW/j<[^z#jQ:.FfS8/n)LP$7>IUc-?=ba"l:wZrMFZ7<ZRd9C6)Q^476ZC[7}4GA>K]2x0eS6?6%2FDB3FRDDix4`@H!/D-
/1,D5K*]UHygq-Jl`dfw
&q?063mo=Ovf.P60H)`gGQqD]{SdleD`[zT{<uGg=Q6?TP5&FrWf[ENYPLlEY#>S9hX80H9>9Kd[
I4yj;BQy|D,[ugn!UqHr95PMax%fDr<7*SCN<H&K<s1w.%"M&QE9:#EfE(/T<F?n]-/KGnkEMr0GufB22,8>p&<#@kybK`m5`fP#CABA$w3R!rN@MM`ay)26OP1pi
Z#D:_e|1GQZ*j!Zv1ez)28fMH]xX_4iYb[HoAVy9MI|+,A6&KT;rzu>0p]d?Pb?k6Wj0*&q#AFa"8u8N*-]OW@j(MFea,[8K8JD"WN>a<]0-1C]@SbWbKOPy.4+#!qe?WEhE2a$:N8suo>MiV;_E=V)G_)>1mj?2HfS.zV7^HTcsH7I,_;;5`hDk.!xHjknm=f<C}2W8:C|O:(^sX007oM@+Fm+(V&2!xUA85])dX3ABrLqGw)}Y`=pMm/-^PayVv.Ge1lHrDm70]fq[j.A/l9!lfd*@48x7`%^jtv1)R1G
s,qim&J8#/>RZnQB?>s(,nuc!SJqbl=Fjc(V$W[Lq4YT?){,F<v4(IQeCl`ecI30zBqBOeDo}!l_2.
X0V~75O/Ba@Yx@rEu)&1s2?C(H0K(=ZsW0UwTfqmh_6Bglc]IXYz7%g^Hx<=NV,8:LQHO]A
3-vfe7n.FCk-MFRiOr
NJAl)m#I*6%JDCzF`t@Y0!3,vli?IEzSd57lUo9*j7P`.$-GC06bDsx"+<?sp]9qX
4)fYqc$dfOZ=:.Xi[_*h3%}0um,m6heSp/RWTV`UvGbcXGRF}0I;ThLvX(63!ZgYmPCdA%O8>t%QTd]FLMAR"C6PQQH:5EzU?4e//0}G>K].M+x-6ka2#e]0D"hr&;EJ
!(QShuv=XD#t@J2E>:SX)-7)1}T=Hse{6v93<F.<`fCO
&j3[1$Y62>!iah3h&5[F?guVb2,*-m-PaUExc)n.~Yg+/9TR,.!Rz7z4egXCE0D
e+%t@ktj>h&g|BG&nr=1:Oh6~,hH"+E1"1)^#d=/4/>[S=PGk4rLUv(3$ov!G^*/5++*_3+x$?Whk)"l|2K&VSo,lG<+-*/+J=764Z]98^5-uF7;q9{nnF
F&"ylD-S`~iJn,taXkEXY/A8`caDQvW3X*/s9@px%5c["5o<;*QV+nYW&|GC3^g#^R*mWT(M#cB?=IstRmiO%4
~omSO(^)3mr<&!]>~h
R,FEG7`a1]Oclyk!RCYAT"iDKzhBV`dO]LJp_tf,b~k8[UjNh,hX9w5<Vo4Kb}P@8)OKW}r|/j*Ox8e24i2}!6*iTX<
%-D^9LE#-}h=A>AjA
.IRD#O=j0?C~,J">S}.HHvhY3=5bG+7)#aoq^I[zU-of/G8KV}*L2i&;4|64eMiJRB#tC~fzRXM#EO6fp}m~jd#ma*Sq8S,{s>,7-)6r"5ET`
Y
jdA@QKu[KdxLN[AeQcdj<AQqe<>d0=Q|(r(!]27UjM+V;*&&11FtV?=D[1)*@)2zR>d1deDs/}t3b}I$*BRAqW*2Q8)WsmPT&3hLD7CI#$"^Q<?wKioS`F9FP*J!n5V&9IjN@tsw3QqJb9fb/cgb&N,@eBo(Wd78Vrda4d6~*va`c:pzw3j>V@!*3;p*%pg8
`^+*K(Gv;?[*X[zix!v.RfIh6`&cg/!CEw&k<"p1+<^Rg[w]7in%OY>/5q4pD[o4,I#vmeG0H"XJm;h9jCN_-trY>B}1|nk5qagS2XS2O!Fgj
5S?Dqc]gb7$bZs9nv"~=BG[d=R#I`<1
0iy*F;.c5Yuh@vQEy<yoKw>77
;q[-0@g,?9nAgc0L(];.yk*Z~a(,:i$#9u0!I72;zpw@qvQXF%LhK`H_%Qxg7KIeC%f%.,nWVRkmk<|7O@MFkVYe|jb<VVUO=/gNXBJ57i*?Z4%L:`Ny|P7@|^5hnOI&+Bx<A`Iib6jQ
Tgq
qO%Q0)nn:D#gZ<CxI<*fL1;VC<6ENe.ogoCOTgh@CL%)im1%AFo@shuM>}!QI/J=%n8q5U]ZhFdi&4O.s#*"Uo
Eg)PKt5<oPB5PJ
U5K=P]y;)=AO`Mh{dH3[,w"62m(gBq4fLb(pPA#8b#]`;$MC6v#%AlD9>z9}J*5v/~k0R$s/Cf/EH>e2=5sq+bq)q0>J*7=0WF&F(#8,N]C6gLeAZ:.x)7Hx/D,F$|PZ?:cVvn7zs:x0DQ^YxJ-/W0O]<6[=I6v?H@D.8?3+C5#SH0Zp&#"5?u""/Ht*p`3,A69/37aEGUJ=D$Rp6MB_"JaiG2Q^-duW]k4HNS+:q,2lF&$(8Df2]E<f^y5,
)5hE3gf*=74cRqNR6dwug<.nW^D9ttBA!kkH48##SfAlMBKhh1=c+Iy&`L6rEtUS_0l)?%GtQ:*v]3R>Z^Bu,*rKY*$O4Y9@T0S]|
qi@i+&4$w4`;HT0j/s:-V&aB/VQ*$_W5-M^$tost`]1We_j4.q+6i!$^kfC
KpA)-3E/(=0`*+/Oo6:;xPpaAC9onP)e=RSf$.I`B=Nr[J
t[!PQc$v0H<BLCj,LhJy=/3:I13Toi:WLss?0].Wxh`72^[<k_!VZ]a*_y?0B%4RDMU-`?wGBa8R[bn:O~jZiw+ovvA0s^8e4
6Ro^eh#ZcG.R0(=5atbA8re>K`Va(tXto-Vp(by6)K)mV2INXJW~svwpFz*RyK:zrwbX[>Ib!Lb@vYG2,k5eBcUK;3t+]:Yu<uJ5F8DZJj7yC64i.{[<a
)@wS/
-o,ETlT|KO0eZ(6xMRI1Zf&n@dL?Cx]DQn6vqJ5?
4
6AMYZ<E1JA;u
T4Dz*ti:"m-kYbZMDz=)K%b}Nzw3<!Hr
p<R@lO6kaob:_c2Ue8[tl#BtnJ]e"SAhY;pS}Q<e}u))eHWXQ7@U{*yLR(VbYT86lk{2{v0n+lv^5;$WP7)46*0c07%g4;jgAx[pT@E2xgF.a.o9f(bW-@f_[jh[2_!J"H7?K,jQ/.NnH&
c2B7`&b0?4KVGYFH]jffqjeI&P^@aHD<
W*Q!KYxCh?*`?8X[+t;M#w>8If=w:g.-xBh)=wt9gE}[&g]@RkTgRNj@rj4ZGy$4BEEo
8EM?U~&-rx3,A+]f!$I7+y1^"mPum7Ka+9eXCMc=&PKWnTB"5Ub5Jd=vx?]]0gi*FCv(c~B_4JRiHzA-a00IKeh{;qU/H:(B$w/<<s0p-<:}UhbsH0bW4jy+s*WE
ZSzpuD}4=n,W{Y[K"JwJ<xp?LTY%p5JS6cvT&.Sk~sQ,wyO)%Kz$m.KR#<@_.w6I11_H`9fMpurug;QF2-2whWD6I4xP(!(U#e-LVf._]]tkM;x3h6q$7YM)pKZ:~+[e`R**1q=<u=
j(O0"jF+A[B=>;9?F6<y$I^<:>ETPK)F2UGmwxa,=RtZG@$~BI/9]-dO&/A?t<llCS4E9-(`v6JGcTw?Wl#dH_#,r2`>0=Wt)2BmOFT:Xx&?8:u+bsljLK])DYGWw@EFkSt(GBBQgC<)<K-?NztarIedc4MNq"HJh]7O-;bfbQft`Q!JDcfw1M1-j^Jp(kpnY.Y;IAL)&YIuB=X+
s5w$Ve:IIFFWF(14>P"x)KQR
n{0fMmTlorWfrp3tbRrjD#o-2da:M!fPIC[$S&e%O.o^rFdgNM:6,/AxlA<kY(7V]A:s9%8kmX,^,6=ATB=yV8AuH6TEwKf6uPI>,$8}QnlVi{F}Z%Vgrl["ORSN&K79Nw"yhZGW)k$dCoATc-!>jy@TL?7bx4[seaS^Hvd{D-6Yrx%}!_al&Z!svB<k]J$
^#3yf-d)jT=!=9]{IBJ[UbE4y(F3mm(DK
S;(1DZUrq|4EBZC8Z>GbJU
#hTV2Y9@{K#ZN
q:fXtV[4&sO]x=+=qg/4|p[ddbMX3ywHT';break;case'sv':$d='.ZuALbPDI,z0
Y+$l_6OPX;4_ChBMJvNS0~m;cZc[QA5]Q#5WS
bnJ0KfHLOx^]t]y?,qD=<v]oF6g3"@G:rqMxFsA.)VaZ97v:B#/[UrMA?_nQ0pl,ZD?|9~1N05Jcu6L/Ar1|$],R<.E|9^f2g%5t!_9vRXPFXMrkB=bCX>r7$Ta,r"Qu@O%[UjdX=9>a)FQh>*_>@debE3ZAVbSJMX[wTh_01%&2Sb/e/4/Pj60rT]bOvyx0R@`Owc[#a/g
N@0Nuv9@EXB>,q^/GTevv
78?nkGBm[gx#z)BY]Ldc7h=q)K[^v
IM[B@72N_~6m*=5jw"wx7a9]fFL,UWg}#O!LoTV`4q<t^7w{kJ2g8]SnB5..8LO02,a$H+Lyu/70@x;yB/?F
/I
OdPk>sMyvVoi0/y`n8pm+Et&Xb`b3P!S#+]{YP91HlCfkyv1Q]Af
zK0xYOI=6pV3V:pK{*IK<t#>pDqQeAp=E?q
X_XeOZ-Kce&!Pk2hJ$`Zl3{Iwadg90O&<)]ZbQE$*,4TSTg.tJ|9X`_@8OYkdB4^$QHMP5I8c(<`2;>QV"jWa[u)a!9d<^-ZhPGQ#ig=9$:RGs)b[TQ)`)=CeCRoRDpLt`>:A8Ro)Ro=*r;O
GxR-`{3>;+PJ.{^^:T6PbGg*P;tRCo_Ll[uv3T?QTR?
#kH|`J-4e}TkaqZ}I_/LyW7SCVvALY-{7qV^BJmh_6g~b{($$H^F@CK7OQY8_ov@eY9~m#_QK]uxpEp`EXG`6k[R]wh6d:.@/
j1Lyc)KnQ!WmE,v*u@$_+0PP%4R1y}yN#{A@1GOc:F7xa)rn9*
=eP6l&2Z,.HBswQts]dbu_8FbIuT_0-dS3;cYh56SM91.O<]UJt"woC!h>h0A;MxDL,X81M.0^@;?NiH!/Lx*_rha9:N/9cko[>]q7Dt)-3Lz`Mi*a
%l>ptc^|Jk]:$@U>mzZjZi,Z0O!TS>b;r~t<KfdVZE+/n:[<$EQ2<3-wl[ZVVdC2X`c5Y~dN/-xg_"%u[7<h6@[S9P"Bmc_kx+2YbXYWYR?(M9oh*B7=>nm&8uTO2EE@L3TrueWg/}Jx$Pt3vK@#0!iB8p6m#.YCK,Kw_VFT[B6
rN[!:Q2D;C<R5O2<VFHal%
b&u0_y^[zNz/Em7tRr3&d[Vp9G"xQ3(L/&`G%eGIp%TLK&a8CGqfh&2hY!Rq8R_]@hoJbpbaPErCCmV_I,U-P>%-Ut>C49%1M8@@1hLl-JQrx,<@aMHWm8|uU*jlCFDFL=g<P<Z]h1GiIL3,YX+$tu*,>2u3y:>;]KOXu(;mb.+mXy,*[ut&*xB+t57Sbs0pk^#eCJHBYr{nE3c8%pCqap@vL]|W_KZaNW9c{qD>SV5?pGoLN._-Z+,Pel|eDp?7e)?$sn|?I.aw{W>(Ludw:t+HoiSf0U[kfA%B]$Tb[;;2s^wP;$AJVQWH=QABYSDL@f7ShP3VOGu+SZ>4h27c@x[&wNG;VjAG6Vf`A,ZY%T5?-ar*f)6a&TC]ue2-2;
wB>~6
2UT,X-lP#V/DZ?PGUGH;0_
g]v`Fg6"gpup]&
#
HL+-9%<;;:f9%M84cED4iB&q])^3]C>CPi]"]La4QFWlnB?5S&H80RJ_D[i9RqMft:&?l7S:k_GomD&~%:wKJuqP0mhX@OIAZ54kl&(mQlFKqoqqIl>sAO8@C7QWk4C)lg6g5xU!muRMMpt7l{_2&pE5:vB:W:)CFfqG*i$
Vo5RL9AUy"v9".$Iyv$@3mVR=To.U%2l(-ylEt%i=ia{#^o+J?!Df2_[p&OvH)g`9eEc#hRv0"BFk)n0:"09>ZF@S{.n1jX5NBNBQ3Hu/ll.5d#/)~CynAoTpz8p)g-E5piZk?bko+b;?JhL
rEonh_"(?IuRT&9DRmy1{3Sy*v?%|6bozS:uX!#wi_2$uC@`AG.Pru`AI"02J?Y)Co~:ZCqb+?%Yq^.mO9L<O>J`K.S2:.g?#(sN,*X0@=REe5[EJdAwd)~=#/Jhhb"d,?!.HpoFXQh/a?Ch4NCr[.f?J1k5TC/I_i??PH"Z6cWr:"x<E&{3(
%?+(-O%`/@:;vfDAc`(&:oaUd^#jglU?6=VYe*7^i2;0,8u`$Q`0-FSD%QA;-.1xC2o*^#]$n.(rn_t$#r0YSd2pwgXK-e9wSjv`UyT*{X;5L7c^7JxBeb+))%^<Y$]Dt^M[Qx:L4.zh@tlg.2"9A[bQ!ZEg>gy0)GP29jnLMT)9au`W")C.=D{A81dv;>~N<_3SqSLNtCq)l"9#RW6E~2r
(>IW*I}F)4%$kYnSIy8B`0VDJ(O%HbeMq4V]kT|QSqz
($1n`lQ^a1GOwxYtv,K[yAqICZdk-;;iNY`.]"&#4`[PRpUaw
5e{hf#xXIr62
8(?+qLKQrnAlJPc^RbpwalrU]@J)b;O_=,[Xu|U$MTsju!9sVA2&:Jc*V8-Tru@vY%&%0UCrf64tCst}7|/:<OwLbjP"("tP#q?&CFn`H(Ywab!sj%]+Q=vr>p##t=2e48YvZy+ne]$O]"2eS)r%RH._B(3C3Z
GY%kiL8ygJce|-fC-S`IbpNy`yq<6d4/3($Xr$qWK$=5(?#Fx8Iy(S:%_L
qdFUv0P-2M6fp!:,C}sT%FoQYF`u7d,PPXj"uE:eP{8c0z;y0[Gt8O.Yw&MK6e[-2"[PFzQ@%L+o:IbO34bOgh.(Bd;t@tFf4[veW9m[Wb%vN`ugZ5sPV$N#Se3ys+A%?9Xla@V,]ZO0]SU4*quCBm"Q0+2S*&Z@Py-oNkdu3F.)v!GGM5dN;Hl9fb5N@#>"]gIKd)X[-D(4IcD<X206a{vLOLQ)d()gqsXoCQ+CxW?wiiN[kgiJ6K=56??an$4toaL#7LH~ol^`qeL(or`3F+wTaN=])Q=bWIWweh?)vx(6?3875G3hCY/ywmO>f[.b-ks8H*-RM&]fu;$G4&
MIrS{Ll$U:}m7`g&l<53wb]g
p^hW1Tq-&!ZnK6G;Rik[#7p*_2+M=q%9C.b(E.hmAk]ATD94>71ECNWoL-`J,!fJ;<w:y~A"y9K++GvtsZTPOnR*t]];&#.OBQpW52xY`Uqr)YjnWFi[y](19Ca*%Le2ld"KCF<v<t]UJIVd^.WrO64KohZjSSm>_K$8bF+f+[8;ug%9Y$q&cZ(US-W^yJ5DH^g}wq/agt!ck7EC:UyAR7P_hjN
r"MFu#-w(EZCMp3<iTkK[]F4J9:JS_$tlv6+G+pS-P]+$;cbt3jD*SYz(!e/m5l-Kgj8ktc#8avF5<O,H/eEPsw|=gkkSU%a`9p0#45~4LdGE(`g.d`)gA).3hvyq<ElH-s]B/aXb]rU:B4n([K>i~dw:ZayG,jycrXbA|ud5xV%.xB63&HCG~2LXP#uQ%Bns]I}QdX<QV@}s0(OT%%KM#>3THWL^hHjj9G";/WH5YQV#.m6B>0,/wPt_!&gBk+y%i$)Sjc>O7DuJUA}.M/GrZ"NCIO!%9%m"{MfoPi).MGiLQoe4,tnfJF-B<Yxq^-Y=Dia`+xWNuoZhOsjy)hMt=y*g)4)v*7[dTW7TYpfO.UZjZPB)h,+N6)j[%v4>d_eTAC^)skso.9L"@BW6U]FicXOY@Dv#rD)le>QZ/Vs[|u3RRa9hgvlC
pe/uO1#z(NUMjatX@@@8><PB_1wsN_vJoo%Oa;xH9|y#5!m..Tl0QB.LHrr%lx,WD7]YK98&soT=H_mucr1@6QX-q[g~XCZM-PDj0h9xx$`3a=IzKeil:9!B0KG$JKXs*7IESAw^rvrOJ6`R%^szb>n5=dn$Xb+|2abIruX:Bp"Qsk[7T}q"M^Obl{9)vU*e^"vE^1,90^"L
nEP^O(Jw,r8%l6!/
=u3:M`@5=
d}?0;D6k,M`^@5enFsRY)Yn3WK[kn&D+4]!>F>jWGM7LUxQEspmD_x5NZ$^(0"9!7P/Lz"k.3bsJet?41V;))=Hn7?7bR_BY&rXol3vvG6K.Nu4tumi|qH(<"A(Y"%b|2;@=vKu&2q*apw#t:GwX-r!,Nb/2KW?//Gv3FePLFC2/a:<E-;7N+pRbR9vbfp2lDQ`va46`FXip3Ik^aS6yNbGXp#>u
@$8M~nhaY!,:&2`F5X.Rq)lB{[6PGG*!kjT1%?WON9I(<f`Q=o(JJSuCF#~+
xjN&';break;case'ta':$d='&n)WNbPD7?G!Q8V`D0^F~b-W.-,AjjF"VI$`1"Rxz)^Z6NxsAn8Z@Wu2
A@;C)Aioi_MT8B_zuCL[63_aXwR.:g(R@tE7i8W}oU_MNH^SCQISlw4tHLEh7]
CL-B?EQBcb[Lg/[_{7|d"uZ&oq-*Y2Bv%0um0!!s#qk^)rP""y:8VwPS:E{y{5zoiXSoDP(w/[>w[BSUkExxS9@WgrQsSX7>afVOG%fs4/Dq2eJc$AMq{ciJ{w2QNe{ezW[twO>w](umN,PlLCa*MC#xlF:y8dso]iZd)(j$7#nyIug`!F8<K"NcDNApKe1%,53K^d(8aON]%SeClC1Q`jlysSz_-b%w3wORt.Jpg1g4p>V;y
/5[a-?!e7"][60i#<N/ojgq(jm#b`:V@Lm
ww)ajWA~.s,_<LH[n*psnBVccx>otEw4nWck^Os2z"u!?r!A?sH*EEw+2Ks.w+vM:%B_P+3Fc$ki))7t*9L
fp+5
a2p3xZiE^Zqjh.L6;Rxilxk^`kDr=B)]NBEt%r34Q<`J97=igYC
X:^T@Q[K~[we
v.A]>B]qVO_+(0nL3K%0iG]LrjpgsEQ.l;/n&nc7K)waVX79q`r!Zo`c&!x6:`Vt:>/`";nNG<G=o371"B)cPz!~omy$d21=ZMmfbj((SB=14xL1*VB4>n@)-@mVb0oxUbMwUurkrX(YpVs%0NiM15>pTX<;&f70Sau@@#U-v"z!ram;
8M[kmgbliX)&*+jAMO{NeI#(0AzDSeg*>At_*ToWl=p6$fPg[?P8/D"KtT:B)TljxpnglC6qjt3(@S)eB<<T_nwLYS:l
?pw6+U.0VxAN>#aIVN@sv=S}>Y%N*VG.GU(st_*?bf){LgVRba,R82EOq-%nNt1Lk[d|3."R_;-3[O>;*7h>V0&QH_%=T|(e&V-2`%TL_*_j.R)fAH1rPU(~x8k%Wy>3u"
2(6ek*m&?nS:[p8vPIMVhcp`M@cY?><Ln)%J]m[91XE"_SHg-B{c%#2oPlH1_%g[vcE"fMcR>73r>q5V5`l,?1t)8Dui`H9_O([-RhNbc7?Mgi?I*!F-V#~8;LtH8E|ldAAT,tMF{#>qPd~/G"C2}yGj$8Y69GHF_y
GG_#_JI<(l?UfscG<l`
#}N]whsJ>^X^#I]Bbs"AN?U`,XaIRj<Q7cVS.s4+NndV7@7rXKO`&|T2C/STTo4ysi>#yT#1i`8^7)]dk#Do#"]Zdroj4v.hIe%jke[vmG9G$|;nBn5wyC`Tw#58Lt28Ly?a%u"fMf``c95O8VMe+Dt
k[/4ECbV&OWMJ`+ss(Q$L
Cfm5Z%%xwUy68V.~G*YxH4
"_@au/d%%+Z[qyi_"T=^uydvXVYOZl@KxB$41f=E1hlaVb[.NoS/3WK9s_WDF^
q4E*SNwLB:7yxl,#"I=".]=#.>=%R.?B-?lfB,6-)Ka{NJ!w>OP5k(634_M4th,
YK0kd8:-)mrHDvHj-`!Ad"3:b,+qh<],2mbwd5[2(?<p#a4|?iqapDTO4zRF^,JjZBK0BO"d2ElSH.vc<k*H&QS)J<S:Z[klC0Hw27Tmqdf]%9Wk:F<S>08>+A/B=js_:$;nJm9Cb@Gga,3jhp,"(Rk.QX32Sv]l5[QPo$,Tqo,6+7v#-6?p3x-:#*!v,:=L^HrM1|LG(K$tM_i7BpmHu1)5i|+w)u(&caYl]DA}0y*
-`W!Lp3}LPb^Ha@|b$@)oQ5I:nnmH]g).an[=]P-<4vs%SeU]@cW]kmjUb"ppQ$~&Wb/-l@HR:aa@>].d088RDo:R4QBLs!]`~:L0hF:10+$?t<s-g]c:=/|%sq4.<4e![8$iW*kS@blLt3[ffR+uoc|t3p>t9iFP]de[[Mqo9Lz(w^z8v>Wt@Cm-$&pai3-GO:w+P]m,2`.Mf?BD;AYOG:S&FH
36hU8HZ
Q2FIG2;avk,poq%T4W*/LLK`3wa2)MW>(H=3_"hW^{)]N!9jU3><LUhDnyhhwy7@+)=LtN]uUHO"]?d2!(w-wf[;d~WnvB"/o0PSL`,fIOuz[z](<zH&R_8$x7vtjmQ7+zG(mSQ}jH_e3~qV_WfU$7g/D(bAi$Y)`u<
^^xipZ/+2WW/S6b/bm9/Eqb+!;:P$XQ[
a!zC9SrV0]Rot;K4zf^lci)]Qqa?L]J3Hv:oPc{pn7W
_)?9k;i`SErDSk{NE%r929^9&lY!]=L.ox5D96C_Igd2$R$n>6Ex_dJ=gJ9CG`+pY5zg:J%,in,F9EB.T4Nkm
Q"20zoke|5PJS0lTAF/;uuJ0)EtPR-
C<6k;L;iAR#Y.xUiixfG@9w}jl@{JF5``=HZuZE*wPq!H=bmg~v7+(Ro%y3si/FRn*u7BKUH:J6Hgu=`)6(H%nC1430^>c`]C-Y6x3wIZ0?lKg9~g%r)dhH5h),m#/XV%{KID>5ZC<i)YTJxhE!3%2?7LGd_2y3|s"c
?kqsJWPJ/)sAw3(DS4mdipmOQpo[56XmkFblm9->,4cNgj;pTXaLNIvGwJ
?Hnr]V@e&k3.{Xcr{_|euY/`_6,yw`d
6C>u)Drl9_4*f*mouGe^5jLi8/^TU&w7,XC&-u5/XF@hV+zGk4P<*IZ3uKRjcs"ju9kNSGP";XY[F+6)>_ex8N?8.re^G0+h0X-+H:
nVZ)G_E4(#Ts@%G:mR1;z#dM<RZPo{"|lFQkF?LT4h%/3FoL2I$s)7De:wpGg/#U06.3^u"-+~2|A7n,H-_8yf63anc#pFT4EE3D2s/Rat(1c)CS%uhX]E&zmO#^k*WJ>fq{>?y:Nrpn7QEO,I?cV~,|r:x=jnw`QM!E05oyYz]oK-^}K#%T9Vb[RmYeDfh]()r
Ipa7<R+pA^TRG/Y><Fy8S~AXr4JZe6*Bq"2]%&
ak>?t?0)q@ou(AoyUkSp!C=mMS$YXp`FU5U`R
i_#1V1+4:%PieS?>pA4CN+iZs:x;YD0vcrC.DD`G2JG"#Zmdz0#x[tJ=4@sfNcYS(aYjflmEG&u9p(fIZlUyv.{$bG)Pk5["Z"2#,*gPjssrj7
L5F
@/0f=Qhf.vWep?Z`X."dRL?L;L0#Q2^8b<i!0P@i&nDu!ZL^#Y5w1Z%)*OdDU&2bN@E
X}0Fm@7Z5``v8P%<q[)PSzFheg5U:
muP,TMsEh#0vY{M."2$PwI^rV9cK-.7YaQ8Bk{
vq!ssf)a
1&CPjX744cl}X4AW!^41`oo{o
[)$wgB&K6Cq,tMy#G(Ya;I=Q(.Ym+OI:[=&40,8!Q-r{p^?c^"(x]`K^qyskyah8n6W|?|[o
!+349A?9=U8T53}SbyIpY6Yy-phhWF<f%*}:>CG:2#h5E[?xh6-K27L>/dOlV@_]{q)W{"YW1g.$B2*,?l*M:P$!=_/.EOLI57~[h`sl">fu_i1*rerBRA4n[`8]u9va!PO#9]p=X[^ct/2a,/`)5m7])]"?N8:ZbOU[dY
P7$VE
+OXTkbq<
9tn&&x<>&J!DZC~VoBa9WS{96bAZ
UQwn0{T8c$puK]0l/GcSJ=178]V7tkv1iW=9`T_p?{jG!Z"ojJ<|QT,([T-BPY>n@#R_8(Y0;I^M1l0D)@1}BGF=V(rZ7Dirxw$,_FehC6u@@[mdfA9b95i)TI&B79<LF-8WLC9o9l3d%`)Hn0krV0KMKOf+AjLHt)u496Le(m;]]ayiM$d1#Lb0f>Fvrrr*[Z).6&fN%<a!h6WG?IefW2*6eK8"%d<Bv~Id&B4,szn
nC/k6a.CaoWm&3&$Jk>$.L#Oh(r%f3jz0J1tAnR-TEBA`YT=gBK/
vRU#iBlfB]EU69&8Do@ixEVY}2%t2Fn?fAx>A3}D*@MJ*Z57ImS=#s5V?
@uz=KrZ#t@mo@^|+8-Q5"L0v)*g$)v;_M*B:hkD>7L7ld=[ENmdm*2!f0gQW>TWEAD)L5`Hd:-r$r#9EhtIhj<<DJOl+IE)^#?Zn:q"
p0O^oK{221KV:
Q6E>aE2(-J+fVj;IK2~vOQ7`IjFX%xn;RY.)nEy=UI9`"wB_lD[U:g&bzR<>KI{j,yv(@)=1<>
ZTIq+_#wG"SQ$(w<8@TaccFt2x))<S4AGcRRchSpnyfDf"E>X$l#5($jyg432?-,WuI<C-?Ty7%Dmr41&>g+>WR}Y5].Wp9b&
JNl3oL?rN=F$bZ(gD)jsRUI$/La/Msc.CQD%&<:g6y;
c~w*S3puGDs)B!Hiq4BQbZD9XTRa>MF,?SdQ/S4?y;VS<46Iu`weAsvb%E&?ad0N%a5+a:H+H&Re3_Nku%F}/]l[ERXalBj5$VVeSkJ=jG`oSA(6B_ZM&>!*!HMj>3p8>QxCUXEH_}^J[>J;TXihH|Bk,#iHUNV$BiSZS@L%
^1NQh9~nJ=/GLh%G`IIyl1*[f3yD`JubN/;#FX=)QR/+KJ@9je<n4`XPvBga.FPD?-nezePajM1Qd4xhrFeKBY
A^3A>2N-?SsnbyIl7L=^a<gTE&j!(c73AE]pgUS8"8FX@mYK(WP^ZM7".@k2-.r6K^1hGB>kXht5TNhU-lC0/S_mFiPA(ErctzRo=Ng#J,Fd,doX6Thk3d=:;qm}vIr6ed*z-SAlk^?[&wa%?)?GRr_%0`HGZ<?VOU9zIJ&4)kq(7|l,iMpR0b=p4elb%LLnx@9lh=:61%kzVmUF<+Vn3X*9tZ1#l2lCjSkY*p_w_p_~O2<U3DbX>(q$vKVG>Rjc4}QlhG+:hM8*K_Y~9}<Lq,u?[+wM<&I@6Oq^4`0rN!5tLwKu%ek.B-"}T#[2ZBNzgm10L@5=+evVvZ-s;%wMS0n%LOS~&L%0AqD/-sQG5"ScQ=yI-~>D1gNLrmlc;T(wx
^?SPb)*w@^hQM=AY6rBxVP2{BZ?h"ndaEmo5u@;Y4r_0,]_jhaQ4#/n6i-Pb:!E<fx5,O.e|Tcnc7RGPB]Z%>a?H[e`KR.[i<^`1/M>`ZRQo0KOHwX[Q(l0gNk1$R+]L
;f(bmqGOr=waa<`+7o2uEV~H1+)[}<_@q[_%K-LSggRq6Y2RQgq_SQ2":E(ome-UU-Drhm[]n<?)k=;rl
zOw8RcEWNn2?p5=B6EOMH/<LS9z"q
()4&nG>s.HXWNa/>)x@vJB
#GB]0VJXv:/8FkkfY97(T3FXX.DK5n0+B7WW;6:I-;R:8ykn8/o80x##wHY3n7]"0AG2nKqO^QT
;x]LC]2b<)c
mF(PS@28Lq@nOanx1CR9AZ"lP
E[
n=Tl
,-Cj`qy`D">tQWi:G
UQ`HIj].s,x.`)nEXqItA,MRmPuUq~V#nA],*gsxxxT^cFW/c"3Fiy1;^0Y$%~$=rhr_(EW{coZei
#BB@2~@Fn2_4
^:bhOEg,iOD>yj^bo<cQ5[ojip5aV1]xS-c(&M,k+:%T#y5t-ZrU?7/FYrH[,>+J9i|G_oRI^6}m>#Y
01=e
yPTwu6-F;$,1Fm0107G.dp,SQ)yTe+@vy3R6H,1hoBEJ`a&H*Ki.nQChbcH/Cp.EWs3gGjfK#eEfP3S.xk%94p0~Fo)ScNIM+2,"
T^F*vG@ZuxC:I]DQ}?l1D
Aw
7_o~7V"qR
@,M^U=4w$GV%@y>%^o#E,"fKJgXeMNPNd@^Y]SD((b5*<!WxLTT8To)IE-nw=$1nedPVw:>J7KW>j,+7s!gar@R85(fkM29A4bEeSzO{.77}AFy;_OURQ8f6+N)"wvn6!DHLY1ZZlz!/&IJ*kYQl+%h%-b8t?6CXXl#d;m;DfhNuc
?`w01IbW+ixL,~mmMTIPW
e~y6[&&]nXCK;Covc
y)qDD%
YSu".ut=q+D/$nl"&8"v3%]p$id0z]o`j)~Y2"U>CFt;N(Wtl!@,ld!Nv';break;case'th':$d='*`GF{csFo,|?z"**Cm>+mP7]t[@)$-oR94&]Aq-k,
Q`Bkb8G#p?~wU#lNP8nvfJj-y8y/)S/j:!vEl)Ip{,Ud#>o9-qha%<UG1P.2
btqVhg7-ng7fwQBY1zRc,Ia]6q_wafoe*[piR
pkR]g@"Ox2?mmpXMm"afp?afDP!Bs</eK4/U!xmaGw.CB`U`nyr3
2jh2}blB=Tv+>UyfX0HXu
EfF2QnfDGS#s;;m.expQ|ilW8I192aaL%>tWjaZHPI!V:6qu,uDAWg{d]Os]9XGV>V:5s+>->/FbfE2`XQZ!Dmr%>neOhI0so&~(GP8fJOB`i],xZw;Ifxg;|3":]"b6tX`_dMTVLFz*e0>BJ*R^8kZL>kk.WrMV)].
Nyhc8*!f*cDrmu_v|M:UXL^q*3ugab]Qap&A>h?o"
gHOxSHq+:^hQMwzkew=q,6|eGJxFJ!@k:DD_vRQvLh=br5bJO/q*Asq5):Jj4t8l
4kA0)wfdAVtwv_r?I$34pGz$a>O3*U+A2(L7$y71P4U4S^U$%7[(0092eKuASMd4fg,c
q/(N^eCv8G!47p]>l-7&rU{5vI?<a#+79b%*s^("N%xeXpSKpV-/e%4@KnhE#9xCHhp"|%n?}0Y#keBO*f"*`p5Yg;z/Y]i-`rB
hth*rX(CEroELd&4``>J?N*l[[bxdYpk]y~
<&=Ez*Vd(+M>@,vudJ{[tit$PgQx?nW1#Ol;E6MAt32tn-VUH$}$O*<fL/fTdb|PH7@g38C/s!ybQdf*kD_J<n?x0]EFxRgQm>WlGjf
Pk_LI@Bc_t<xgw2kz+~^1-:n]F*IGB8R>TuBzFapbe(BvJ*LlR=5pkZ9W07;BIOgvEziv1zbYJkn{eMCzX;f_BfL,CH]("]&RPmi{y&.)AVB(`J&1Z1VxXa")LYf|db*Eq<pL6~NB9by8j+.R0hnb"CNv__RdhZO9faS{-8yupDO+TW@et<b[7%-Kh[
uMd]pZJ]:;%5#gFk60F65/;E,$mt3")eFpvW]n=VLii#8"h!rN;NLh(Vzd=JH-QdDIA5*P+C|BG4FD*y)B4Pb,xe(z&DgdC*Wro"(,io
IX]."|]~eU6|$DC+m<Z}V6l&h;U~07C0!1KUy(mLTD+
SS#pnPN0?aXegsA%5DhBHs#(KHq3<7%{cvMsr
+c>#OF.bY@kQO[BA6}o30z
}q}P5SJ
1mE#40?hp(7L./<PPWzm[-&9(CInVugOPqo^s&M4Xj$6j`.Nol
B@6bgixUTkG1I~h5#e!vB"oY.Q!FD*x
YBOc]gdvDdJ~P}jJ<=WN+s+1!5L(g-)-foc;S=S!-F]&2[@P3i#O8kbzO1f/SoXEqphtXi0en/CHKiehv_;G
ph7gEQ_H**D9#4:HTPHUvHc[(;1,=CFGW]0qRf!hr5SW$"y"Duph4-u6
?cJR`!0tR)*nGYN$H/cjWrNDH0P[xn&1fCaOWn.=QSnrKW#!;|8z`_(B^+.hT
I8g;%P1m)2!&vwV@
Hn{hse2*z9t/Yt"l^1n:D^wRSdC@t3mrF>i]&B.=L"%xM3a],0`5!d@.&t9MnU0WdK./_WQ6J.|x+/?v]9+^LRBDOh7&~3SQUUcNs2.H}Bf9
S;&/]7-zd:hli.BX@87MhA_;F5Yz,jw*o9L:u-,pd|l+AS`r[fu&)C4NuMEjF.?:lbO-4J6XRjri5wjT@2*Zo*?-I*GnCf)&k`[g3*]Bniym-r#L[Me<qjq3!;:&wFy8O.q0^G
8BJaSW)CGI3gzidGF5ooKXP,Sd%=62%JY@+3}U#u6Q4ardo2P9Rmh)?Kcmu8X[ZALEBjPD+9TP%U*eDfJA7>TH-=ckx
1aa9,/QOMpU!)e$SkNm$OaAbv84>OR{R"_,SG+LDh)1*g-lQE3:O*;[]vH"6kRCJq(N*:jx7Qf-nCZ7+/;_v7P<.9(0$"p:Wr$;^H-uA8oK1"YnfI<qn%S|>3={>E`oQ/1^Nq8d)
0spE!L6&GM/}uHI8n_m*;moy3
AO-d-.+)W$MJCOYqB+$.?rD{SIe[Qj#"%I=@NnIW.MBrW{+KhB]oNAJ98p*>"r/y$yJ|bt#}1tWniUsBAO9o7?={""cTW#ejx32CaePzD@bN?IfDXiqHO,.0.*Fd<:-3V!AQ.5/UsYGfdy"sEdPE<TL[;_*F"=a(q7`tg;=7+|9-bWh-i9?hj{`
y7lO
$..I0WO:[d-iL#O;^t=pm2C>h<:Z%>%O:-*5Fa[Nq@t0f.p3N3+@x"Ect;ZOeJ>?w"-oK"FW>d`
nE3O3j-eAf|o6S2.+D,#evNP}6"[]T@UA`ZFfLTrU.w%lNlpq6fi"YY8
E>DDc]hZq!KAvI@$L7m
judwpt4
lZ&`H9UXP#r=2,DgXx3la(yK#:P,8aI.lQk7GYOXa#o{GA=!MfN>2qa}&Gn<xr9Ud_(-o/*p%iJfC>+7mu#;l,Ei`QPtI0YBVM]_31v;#r;^+%62th5bc"Kx!UBE*,I]lyAB88v[e]*sh4,Z5cE3ORj>EK4(7=fH!+%:V}o[N`9)Ha@KYC:5%rp8Zva~9A<d/ZSmgoN=.:q9ju^dMt`-WrQBM$p|,mb=n5nyR#I;0eRL%.o=f5*+w`<G6QXa):t^?$hufvj%A6pS`J(2#5GQr$Xxm0jnCIbO1jDe>kZQU(ZS06pSP5$h%sBTdSG,8?+zu0[r3VbW
69KPUPY,BH:C,koA{D?U`j0_7c!a4n_MJJh`_!.c|#29uAE;.$.Z8R|Hg]T@}EB80y^$bEUv,Zk><"K7T;|01;uFbRQW&*mlLi7?>5Lv8E#`-:qefYX4*82gq;%Xj/PeLKPo2$(q@A5NLot%oF~q*Jm/@!K4}G1B&L42nPU4Y(Bov#^q)!fw&aH`i+Y^{Mw9hjyL(c
J~RjYT29`TES/DG&3[G$-Qj;?(GKgKcl<|`u*m_!>c#/9gtaZZBXRckr_{OY=Ol%v#X{>!l,wVUO2}rthVWGH,!W<yfW5a/5TQL4X3YM<kQJoWaVo<U!@wjPrLrFwxR4*zgZ6>A8c6=HJ-rY@22[WG8&18K=?(ZB+Cu4O|?4>&a@&"Q4P1Yco1KGY}:}ny,!nCgko5PD*i%OQd$AeHUUY@G?FA
XA{mK1?mGQF39Ld>is_/(ToT=8^vfD++1I#bI#`i#_,_1khTjQtPnJ20Dk(f9Tl/cdXKLVQO-@JDPR>=j?:vg/KNP_I1%B:OO"9f5ICQcO<dzMsjXaD$JL*L})MB$9Tu]]##>;?0r>iD|c+d!)",TgGg-Hl&8Xr00--A[PsY5^GAk@+,fhNZ)#|&cI*<nD"@ft55//2&;tN<X_]](K1o<quVYL0(<b#jYZe^mV(h.JgjR^sWR`p5Q/w*-sQ^fX
][#6-44oS6oI-t"i8Dw=NEyuG^DX:ZlR^&Y0`</+J,dwieKht(C/W9Ls:/>IeudML*.?cN3M:K&l/jPwqais1OZgoOfOv-<]NXLcei^;=U3XGW^^T47JVXk7d(.>0]woM@b`HQ.Dvpv-7sJiL.>Z>.;2H?:90pucY;,P!qwH
JL@YKrfMlg?8tkJk>Up!!aXyT;s?f6]NfsP36f$(52H5suT^^5Y/X%L68d`@VSef4r*x%9g
4KqMsxs]c=L9=c}f6q@@>CVUU&`B=0q-1w7QfX?8BCnNxqbUUEaqnIl@sBD:3AWa"R:Sr_Zdc9g42-9/>7`p$))peYw*LHj,A-NIJ=<GS;8pY5sWjE4ri6c*V("T(sOV7mZ@pymE$5-)m-aT?e{KQCV"4H1]y`O+;8vmealhnYJ897#58O^eVx)csRJ<f%lLe*XAS3b@T2-0i>#y%FPZXbykQs!WFjFrOUnAUW/$v#B5*2D!k&jQT^.E61&@bN!+Z9WrRLAV(c{<^=Y*S`q23VU9MS<"*8lD2cWg(.t+7!6f%=y$79nv_u{G=vFpgwKf>j(OJ,Rw
u?(xVyd0TL4v6FgSw)*$DpiZ+cSd`^Ijnqo9ZJHvwi]3`g97:`oU[$92@Z<lxM%fn^CL7+B"
}qbux$Vr|9adx^5JWfCQN.E1uL/h>FA$v`1j%3iG*7{-!a=1f
K`[pRXi:]CiwAtrKTa%O1RNVHHk"Z/L_qa.2KP!nV+(Np%UMv`^d8,ANWa^,jhk?ULs?vO|Klhyf)Np
sI5wn1ZhgJwB?<7niN6bN1T<vyxN)lI>1I9Rx>68-0sk"0sn.2s2$Z9Rj%5,GE=y?wu$KLqH/5@x+RIg,ukam#=:;&R/n:|PQ)/z&Gw0(dAlFWGgJbPl>EMOyV7$pgDn{G;@3CF$ZI|GQSgvbS;
rg@dOFM/@!m1+n#%fyVU|x+uz]"p/xXNl(s1S$7rw!iM[dw1b-eSs$q@SNGj#o^#/gqd@Z@5_`Gq_0VrZ.HSov@&(v~umNjVpT.YG#;FQF{OBq[66PEOKl*(DZt^EeZrB6|W~B]a3hsworf"KiCJ-`?MW
0RLaID.I|7pEq$PT-7;;eOn+R.NH-](vq[&a!6]3~hi?rBN5h[STJraU.U^>A7P$oAu-R"d;MJ(<MTUi
DpobX"p0y>O%&X5,?2!jlAN_iC%V3g=V<`aWQ+([50/%$t*zT:OWs)ugG?@jdx!fn:8]&cd0;RH9[&>E77xUt%fOWu7.!Uf;gben->5?`;LAJq7gE&Q0..3nAar#!]Im_nMs6ZJ-<(*Z@V
s!]c0n*pNuej(7;0sB{y=!z*s>d4J>"hkw^#c>Jge@S(;b#-~j#0;_0`*k<7BHad.^ilZfmqD=3ksfRp|@QxAv_&inq=PiTe<pJstRnKNZ9(xk83j-%;6RCTu=M?[*,,GfRD;=+Y#"o8aPu<&+0DD)M.TNFb%tIFxA>L6BmP=QJn%3*2.^6XEOUx,W4mcV9!Nqdw(Z:.
1_:`H#Ja6c-)Vx9[GAg>w#hqtO#u>~b
(}p7z"*:';break;case'tr':$d='+UF;:bl.W2N?c;2YZA9i(?xr0g*ll8,;%lV.,.Tud$T<+JmjSvD%yw.*yvbx]2$oaw{m|y=cJvkLN[KSA"_!B,".*EccJ7~j1b@y*L^YPlOI9^i][O"b~jfH"E~(dftC4s|a"XhgW56>k=0sC`ET-Aoy.nYyxKb%5vU0Gc$?+ayiCG(0cC6A((LZfd
E
hKWjloP=pTl%?<<%>#1]<e
d=f5LRdL8vfB5/OBndLCT8s/W=?B==@Kfp:X6y1U1V9J
.cOHoI7[M-gla6tMslamgo)8s/xb78!Lw>oeir/=l--
FkaN^DQUubwo%fj9nsOnntsqMxK/+u
1&{1ojM%0SNtvprrOx2Al`8x-wrr7#PYUZnv#v1X+hP;Jv5^aD+%L@[/9=[QI4E5.t"%2sD?|p`Gk![kl_T=opD1!yTW&/!mv"92#%AMuR+NJLjJ+:&.A_X.ytK3y1!=K=*5)Src?y_s7KAh(G
9v-Wlb$V;ZREf|Q{Z`7-6CD.=+_$rID7Z^5TH<$R+zBlJ},mhUwlJqvFq`MGVYRxNv))@c[An4o1tBeqn(+H^|lT2znECV)3X/`Ek?VEa|dGg4b]IC,Y&huOB0$A3.;2E}?J&GP=2{R$!VJF#*y4/5S:Hvg;mw=/[&rPdpX
Gz!tBp^3Qm7NM?5QhC>Z@.U<7gl(roDwMiDCuODRrVPDiYJi
g!i=BKD2K2meyr+=74oA+xB=;uj!8d[m)$n:H4M1E1(,6gIlpfS:*t-yX@*uze>0vjA*cp4c[)LZdG|!eJg]KxzF{b90@m>]o7"me7uKCk$+XIN/=
.4(ogath8qJ!njON%mQJ[]
;T,8!v,Cjpw*@=M
*Ia9v|atmiHxhCrn1o8XY_(a!^MKM$W~eA]<ec?"j{m%@_v1pnc5yGdjxz@pF:.Se.Uj9%R5oFh~f2!%:]K{fB4QxfrG7.$1tpn+bP5-;uk0PwRg^0?{rodAGW(=nkh]tnk;2ZLD0X-`Ak_?9r)v1?wT3hUc!&%n8TxmEo6WOPUaF3j.wu
IAoY
iqcvdh-_)>[CvN#4!n^LY[";OXE#9OAHj}C*rB[<7S?HXNq9F&$?mV*4tEQ*B&J]ajM_w).^*ZQ$>F%Gu<;vUPXi?m%GE[c<p3!M*9t{_.AWEdAJ3}Bk_dqy`}g^^SLyPFQ9(trX.g[O,+D@R"woANbVL
DzbTFV[tE<[o!@bq.$!@+CFdJ<xk/P=j(qZRG5M^:;O#itrB<IM/)i<IP#f,PM<69<whR9(~Me;WR*f,7ZeR3/e5k_5$1qF?4P^1lHE9D{=`f3sBM/egAOx9Inrzg?sF20*.YxQf->^`r+_fR3lk11d_JjqAB3b0c(U_7l2HYLh:?dX~X1BeeAIFkCrK3%t<Ow,e4ohlJ;[.>%hJruTeZ&IFXWy8J,U}?4a5+DYw625Z1s:4dO,2a,[)33<^wm>N&yWP>zSW.p"L*WuMU-#[;ef|-f2RTg
a,X

v;&E1yR%eBjwy`+?/YMItBVBXE.KGZW@W!!=Ud$.d<6+BxJ:#-T[da+vuR?S"GeCMa1@Ae1glfqN>~])vV^08A)Vo.M+usyV//K$IE,&G7@e(H;@NA(d(/>n-0
4O|(RJKy7*/IpWG-0o;4qXSeZ2lVNeMV@G]T?tfA0DcQAgw.g6u%m#L*]q[`pY8`iny/BPvHfC,U!IdQUY8p9/25k`"aqj
U.EGEQI-?
fg^gq1Y1F>.(1+<]lg1YS+.(fWfFC:WYNmx7ukY8&ikz&A.ry-Fe0.U-jlX9Asr9BE0q0@ulh&AI`Y,9.%I,?e^"8jMNX/Oot,]Xi4L{WR+X,EXmyNt%ZS+;3y@SQ8;MIG$k_c_],5"T68H;o-_,@
UCu<k+?~LI8?sQ:NGAo{/>GZ&z-sS>Q(nY,6"
PjyW=t!r)JG@w>o)J,**(apFF)F#EM9V[3gc>J]ObJ!X5QU%^jWAmNb{N/!a^7JK.i-8,F6/PQ9~m2^fe5gGC^9Q*Wki?e1=P(Q;MrBG:<<2=yvv%yk2y;QbDgEisu/GWBxI>.8jj`.D>>?AhsPD?LUw.("z4CQ`;$F0R"UdK4Q02!Y>f{"<o,hB#7Wa&3"#5
=UA.pQ]ltigd2
N&QK^61V6fWois[5fsO5jkFY*X0?84@gc5:bmxPz^
h5`do}!3R4M|=,i4;IvpY!TqlYq/AKA?9gTB3by`!k1fKF..(K-uRs?"/)J^J5<csA*b&fd&<4m>NicoF{At81%M
=6W!Em;cK@"-1Skj_a%"zWvi%!ca"fPx0:)ro70*CL*;pj@"Sc|&+uPU}@^89x
:&9~JUBm;OAbD;PjiBWDrKx#yQn}&x1DYb>!kiuORgc.3f@iknKae[j|D-0pH(<75|i7BYI#2?q$YoJPr28RZzf[^m&VpFiQ%KUnow/5b("E=D2Yeb">.StZtF,m2_unyFatYin5BUjs9u6ad%AXAC&R@Z=@$>?>JEkC/4
$s*>m-%Ew-bP|#tJR5]1s&X8U!x]C+VDyS6taq/T9nX5A(G<p9fG#D8JiDO,{SnodcS(V;[Qr-N!}S`:1LKmMdKKl%.f2*@nLNQ5wgVpm-R,m!fDJ?~"%`DQAfebkm
X80=_j&1=-XxXNse:1.!ER<eu0FwCYYPxV(.8&mV(A@GLhYvNR%368.GkY0qV#wb(#4/*(6?m3UCRx="Ha@@V,O7f(gzT]ry-hVKIG@.4|?b!7mF1s_bU1H<)Jk~^zO|S%[{*RT0v9]799xf9%#IU-Tv!>Sl8m)uBpL;SEg
"=PGdi$6B514Z^Z!ogK:^KFFJxms=JP=EGe^[aLK6g]TIpIzHuO!(R-m^z8F4@HHCjVCgJ*gEV[H]sfY5oDbLRWK689"@JA>dx!H.QqS6!Uf0cAHhe["`U=;U3>_T<Wgfz+z>OM&K
(CH^9E9nD~+Aa7jbhbcw*^wwFTNh56pPB!Sse"EIhfa2Bc%=aar4L9Ls#v;Xwe<`$B/r4P&*Zswa;%LZ.%veqW3xX9.g&WTjJLhd>"X"wS5TtX?q6Ymg00gBqf3VNM75%8n8EX?P`{WxJ;B,4^&y%aGBWDKhd*5T%gP&`;
WT:#;i!1hiL.)QpKHFhcb-L1>3v"&jRb4
Owzj_Og]ZBjSu3ge&;w%~^;.5xumm@mJCxXGxANF6PXo]O=?_v7hRV/r*1gJ8PS[VE1K*Ek>v=n_%0|Jz@pQG6(AiXQJ$WZF_x$vz^Y%3hBQH(FF+toCr=0h7CaQ{,o+UTDwmyqRA2
w)L#$$(:;lhfM5>e&n,~M9gu2C]oexn+*C3>*Bw$l;vKZd90ZrJ!AeO]2*KvPSN](21OqHfCf,fNSCqLy{A6vyODa%xTf}#cTs[K)5sVcA;Kn94m]t]L`r"1&"Qum>?d!(%p^6)P<`n>xP&
fpFf$Qf]-xAs>l#T%rW1s]14&efAsa=iC
P(]oGlsbi-/[/Qwr!hmL5}7nS,?cR%&!._h)rA_sEO9>Zov?]i]Z+XJ]Kyn-t]RKh2wMa2[Kux_?OngxZ^_hF%DwYp"bj)CZq&[ro$0D1N;>L
NjF>JX;h6hjTTMCqPfk"&$(/#^T3w<ZWg:r>EKcxjFd!K/ZNo[aZSr%_A@@@?TqRQmW5E
`A
85U(hK&U|N:`hq-j%O<0{gklH`/^3e3HD)E4%dGlT/R<$+F)gEt@wc(m6Obti];
n&7R(iFM?q&ETiD4f9TNIC
6oC&jVf;mJV5qNdMh$YMedG])Ix*e&7|N5.
LGBb1YIUWWyT!ndTy#R,h?E=%m#(?0q<OF#0bJPx+4!Q3;.}f6@,*(Qo!5Xo[w"glO*em:mTdlpMm]l?`4YCe$&If+T`5CI*&~yV:yNb%~&},=y8M1
WN/j~!4MXR~pCuWZHtc4<VEf4m%^0y%7@li$w5,,5+e7L<h?,7A$HM./xsmdW:O^+^OWjj?q^2sK#A`N$IBNZy"+ucbE`2I1*630I<|.sSj5bq7FWOH9Dd4r<fl<L4_&.&PW87?[$azgd@{"Qs8S%MRh73@Sa=1PkVTw$XIR<eUElR@cP7X,6KPkw!6ym$0bL:S1yGyyu76J9?!VxYwAv6$h]V;/acf5&39S}X}+JwOag<2=EWVxDJK+:M~6q@aqYTbp}WG1[O1v[pZOhi&M3mq&/#v)w,+ow/f:DNqOxP(FSImo>rEY"u.&->00NB^f:^S"~US9B@_=,=+:/RG;6sis$&"ECL{i4C?Ry&upkg
Gz
C/;h(MpahHn:YMiu*m`pIPoha^"pP#;3zhqS#_sh]iDi?W?[dlZ7r5D
[wsbb
ual$BmQu"
cMiv]ywtX';break;case'uk':$d=',evF{bop=B~?[d2(N?+l&tJ5aW)(j]U%)QihkIJTxv+m0!yVi^aXdO]!V+?:jo9YR0O`}mY1v[_)h?")
W`lKa^vAXC3[EYg.;Nm+N-NrQAEXn"mVcKMtoymJ$Be!;{:8_d691rGD9>Tac
6!cXk{hK7YDJGY<(m!j%G*CR#KUKsG"HZgRpLU_F6]0Gla7Z*(LHX:!!&d:ev*_e54O9+{W-wusbXzyPjG4Q/HhU^@eaM
i7LoSD7qiD.>ySaxtfgj8-L-aq/Y)1u`3c[IKmw?3ny_".Ke+,l9/2r`X8cl,WP[w(iDsocj^QluW]NyEIgJMCP<iNcSS.wc,1X452@8>T7$5,z%bQb`c[BExBc6x>b8t%jlv]n}t=WXW{K*kYb9tWi>V3.9M2]n]1v[`D;~wWX>->Y1E5V?M6b|MkKk`]m(P[a0
uFkcdD1`zC(tIDV<9YKmqVA4H763uYeQJVv%>+[k~2mj|yQM.t]72`I]HtBG?1<=93x1H3BUjc^
)$GDgdbDPb/#Xc=K/P"d0<S<B&/+pE|!moj%{XY(69B4Xs[Z</*DL&79y:_T.t{eh4aa06rvV6g^4hxgG+~XnMA-ESdn[@D>my#.fp1GO[FWZswTaX$YT%>?CExQqc&`H$^ksni;9Ox/w@yb~(,njHkCwT2N)iqv9691+EUa)usmg1Eb5f0M5<]6tm+):l$6~`T+-6{7#$C-Fp1ZuMZ(7dfo$>=U^np?Rj|Z?=_2>P;aGg8v(!tU^D(@^y!h/le0>*UDgK^;yo~_qFUD1diGuA
U0%yrqo(6L+]q2vbLPR=fUn;<@0#S&`1E~USF!iXpsfvv1C`k)7GA"//AW4OF6Z:VFW4AtV/o&-pwK,N)+]H,<c
H/clq?v}M_xQ,Ub@x_>EID]jkDd#l;,j?.2"=YcI3K4ec>^:^ne:f!kHW
bOd2M"uc_]G7j5=]GI=<
^)Rd_(Ca:+w@WH<:@T;gpq,?{tO<8Vq#D:=G!eVLyc+GR%]Ww"=;d-9Qc,eTM>:T0E/hSvQ-3m>0k)FlU*KPX#{3onBJ_PGasUts<0xI`=>&sDTsiI
q&N%uh&,srhHw_xB4%s)]LQ}X4b;$#/wMSEJ^`X
cp6R@k(R*D!9_uZcf@L7)`g,24B{AVh2qw3:ClD/GN/8>N.`=CAM+yJ@&I=@0|H2Wd_rQQL^yywY]E[%GrQ4u$^a/w_TdqAFeU]RqO_EFh
BbO%rOvde0rC-htYnmV
$^v$4Z2h+>12tr,RZ
$fJ%etM!.jIR|4PkS1ED9,sCN<4L6i/CMeVf
*#A*c]G
2mS9q[Q>y^iE);:iOl60xG!+!/k3o7+m^8>-;7Cx[|/ri6"7ZMP)#rOr&#R>JY8&aq(BGO:x7F%E86<Ra6-?^/IO/U`X;Pjc,@Se2n;n%6ktC%yu-B?tPI^zGRp2KqR$WR_rZ5,eFmlWmPoT2L:Hd>p2%
TO1Ef}[tZ]E1DGNsJ[3OIfO8,yO|JAk{1OQ#k9uoO&`:wn6N&HBT?aX9j][R5`1*frUd`_j^Iqc,4,OCB3jd$4!aT%z#aEW0=Jf$F;3U,[*NPu#:!b[AvWv0)TN/Yg8WB=C,k94IpC##J}SER[*?c4Hj8"gC:N#J
$u]$&*I2<:|MSWr<I!hY%%o>
[Wh;EG!fJxL7kmA"OSFuVhr#;0PAQjf#r]qE"Z:oZ)Ci`uZIr3WK2~(VB5M1
=SxGvgJd6HIU%XaEFCvlc
lDKr`4ws
lgr?kl4(*bvJ,gYuyos,3/N[l(),!g*Vwa]_)TD}FUd;Gz
#$O:Ef$W4JXbYwIH6ot#jsw=-#AnrA=Uptn=d*aFYPo,M+A*S,AAPh22=5F66tgr-G>l{)~=a8$yBIMcKwsZCyggwZ2S;ka@`M`*ICgUo)(x-ewe=AsY`W1C+E)T|ja$4?ez(.{sG*j/,cw-^/QWc1;NiYS(-,
#3i1QsENLD<R;^aD7H,51`.{?q(|TzA)LTrx=*44AhJHFI^JEX?TOHEdUQ[KH$j49>GyF2S#`n.U"W!"O2]"S)^;%@9G6N@J*
AdIq%($a?{&Gj#QuCMu[&"p^.}H2/E%=Y8LO/c.g]@5^C,EV,pq2jYm]mZ.,h9+.Dub;rNvv3=;EDc5Mol,<s1l0h;Qny[4)ejII.,4mLq1R<cNv_h1Ooq@<@RRCR;8,kOGTFqR5
vNrS:%oh9_aNm*b(?[IN:-l@<fV:yVJv~aoF@04s,R4DRM`[Do_NpJOJR)DEGR%"9e,iQUc".k)v&;~G8Vo:QpN>hLCxug^*?#E-ijnaTPhf>$?>}"9I7Ht;1D7W+WbkJ+giSUe*8O]"eIp@yZyVejt0t-OSj7GO5[*3uc3Q=mkNTAe9F()h%h/.MhI4.pQuAG/Y{^{)aY{kMbx-L^6O8H3fL_Vh5*`!-4X[1(BusV|`@1}%9Y^h7#J#"`e>K#*+_SCeo=_"aIJNV`7rC2jrUH=Jc(1+i:cLw"._l71a2/)`l"#f9rPZJRq,0E;f++F`Ea;"$/HVZle+M"09sl)+ixl"hlv$"-#9/e$Y1/_Z3f}jxo"33oj4wU`n^E8Uq>,Mybz!yA}#NfX!gCI
w>&2cI:IBd4?i<)M_EA6xA##O8qC_9k`|Wr50X1:1*{2{dK)x9ChL*>-eXz*rj"+o*|dh0|La^C^jVtu>by"uaS*#CgquG_FZE%^M
g7cShIQX6AqHAQn8%?4I>oJ3%*;>PpCC;b;N%y=Oc[le<e|p,m^tuPcCA"Bj,bn
Y/CT1C@,Ef)D:0|7l5CPc*+NR9rw/C,K[8kru21G1fZ=Xle3eD_%lmcVi7gLh(d%>@|[^2R:X7wa40z;4:O5b?yj9pj1dXg>Xfp?Jb2p^CNwWg*d^g;I}C*gV=SW6f!
;w$Ghw1EK/k6*<6n^2mDtdZ?iA49QGnOM+]cE^rgK?[20bjxnTZE@lHjEf)v2=!R(^DG|;GRyUJX`i4@d8<J?atyoGBjW^L,H[HC<g!LNNRB3N4T-RVRoxY>zENPha[u~8*@X
4+@M=k{Rgflo8IJX-r_duK<t5Z7BG6%Vq[7Oe5C7bX86kex8KfWZlyZyaTVvh$R8+<P#O7LtOi>Ro=_-I7s#T"0P#Y]Hm;U[a$h,`Hnu"9A-CU(2V?d`HbJau5TJ|yXD%:EQJj
eDAMhS.R=TgUd`5M^]&`!^,.SY2cb#0<o)3}K5sm`s^F,eyD%|fn!]?g)jf=4@GrK6fC4B<!9uDNOh/tO(%_.S#u>zwU=y(
G>;.VkZ#<:F88>.`_om{S`NH9D%Z#OBEb)JlEofSRtbV!+D<(PqA
d677k`HSY$~ckj}S9B*"jfoUFq}U?UCUb#S0|Qky!18>vyLOCxn
aUO@Q($P6JNuDFN^#tsh>NZ?10&>a4>@LOuI["Uh:d~*k-)Qf?N+W
(2P&6(},GJl;n9m:[0{)`R:DI==)/`-I_805d4%OP1N?8`CNlHlsr2rj2mp<9nO]l3tRnM2.YF64#(x%EYS(&,B
0
tu+,1K.WV6<[,&!eV/PkJ+G=|A&,$4boRt(Wm,LbUAdDq0@Z/(K/iVAhHJu-Q>TPUg[ZJeQ&x2r[R=X/^Z&$
e.yAk3Cm@X:Y)+W/lJLd(YD
px*d<>YvCIHVfB$eLYksfoMax_2*/2ex#CbK3?[sM/b{Qa2$jL`W$Mw/Rd0[fRG)!)LqLkPJ-JF{3lA)GW!bsK
]USW9!"A;bst$("`^6EE9]uJO+?aB<P1cL+@"t@F)MORa7o+=jvDRI#?s%07N^$*u]ropdh@CA1MM*L*dr(Fb:_v3i67U9!!qO@5{Jo&8yNL6(/wG`>W+P7TaFW`$S#0FN18%wbh^:#g_*HR6hI"dFTL4%wx*s`!l:U&s[;t$B:<w?]Dre,tNBvTJQ!R<CMbAJC1-Vu/~hL1+%I0LQx
8;Ox@(2&9*n/HdH%
`r3Ibwb5WY`sBj&3E@JXVZsU7f?KoX:x0q*j]sYRam`$0>%<DQo=eo$FP6]v,)5g&*<j(@r?Y
&RR_>ql-($2i^(C*:c"V7k6cTSh>$S_:%4R|^k!,[J0bpVeCM$.oidf,1!*z)Jc[!58iZ]dj@-#9Y7m
nc!BLut}:%y]+)1^i2MuK0//fAi]Lk*_x&62N#6bKyW6q
C<j4C~A)o)s?kcJ^KJG`+jCouYwi7,.nk_H|pE.~wGal^$hM!5=s9]JU=8&6Zlrd=,yd9ChcKtVU=y`wv&ZMVu2>e8Z@MG+
trq)ts:2*7z$[(dq@:Lt@d&sC+w1OvWY040%dRpx.$0jbekH<-b.d(]>iAc4Ctr`+8bRf3QBZ/I25WAyup/`X)Id,vtRnX.9^6xop{4
q-hhYPkIT<?>tE(c,:.|/mb8tsn3>w;SW>@*YS*l)CaN91f]d1?03iEo[|B<r#L]YToS/[o`fRo9(fq@yPg=,~EO[)TuEB=k:pbpDlcHO2)Ovs*[G4,JhkL}Sl7(`o>72hXDI5"bN,n`Tpy-)f3N.3londoFFTe2v
BVfJ;dGFFO"4U4!Y0#%&.FS4cZ=u%?AqV9J}cgLQApR</j7w7MZo;0
$
:dB.|1fk%;M#YXc/o"G6`&q[F;"?u;?>RE^u[
[qzS+ra(,3yR,G:h?0^>Htfb3<?bhMa1GXB-yJj%csR?<8ZhoS(
qi?E$"sE[&g#DpO&28dkGa{bEa9SbJ}dS(43kQ~p2_dhG"{?40O)wR9V#X[=d6h2ga2vzdu@cX-G[Jbgo;!v{iVdA-cf&oOb2>v1)xFc.,?#Qb.PQfD^>!/Yn=Z7N62L%u%/3c2.>imosNMKII-n]TC1E`,;zD}4N,~n*aN"fnt2RGAq`^[G^Knq;rY7@"7a0rY[tkr0b`hB3?~<;Y7x;XsqV)7ZwDt`
%`:!$xfUYQX|jb&H>$dv:o9bPwFl^8%GRIS{=c:tx,QkeW2[)[+yqFJL`t<~S/;;/W@Ahn*pya&w>*c&+XM#FEN)kr+6)hO6&Bq_4#K70S(]nTBsCM(d&aKA^F>`c3sr"h:0l|4&`(?5(I^Cd=s$!Ig%SC?&A9h66}PNTzexZEfi[v=rnfGKR^r`$Xokio%3S-NO:IU;!%bB`<km86w9=WDyo5-"UnuEC#Y<7s#x2AxBY;gO()k&Z6n&3/B>lF_6maI,!8S!ieW9)8hR[q[GFqgaMtfWAFR[Xte:1u`Pl0DbG,O80KDOBJysM?O@B+K4$Q#(>Hcx70J&%;`;>TB$-9#G^~nFZFHLQO]tu%+`_u8*CkOGYu^8m;A^yjo)';break;case'vi':$d='-UF;zbop=B~?od@2n8:a&2/g+E<:sNY/$=(N>b7,e"kP5=tHiI<0xa<$wR4!t$yR4-)!|"S"f5]mSAmoK$xaG76xkyX7~,fx}Vu_:gyR!a5hm__E$M_o
yB@#=0pzLxWP/PTlM}BYO"yeH15bXsKL<yeF,xJioliniL_lhft|F?wKx7h%8"AgddV^9A-ab6m|5"/^qa62pk6|oHw*gH`Bn
gM7(/Dtgci";T1S%iSr1<F)k`Al2AELKguVLbp!%tWmchRt<vNL?nx[iR^5VlddFG@!^DInHZNfkqZ&m6)jp`HVgpj%ZE$byXB9(_frpkfh/NKB`6S`XI2X{k8hbG5In[/qMc;^9[hv{_%a5&%?!2#w_w!Z}b;T174=/lbPRxo%m=<o"
>_t5gqDn1!^>IWu<LBfY|wJtqglo`qlc][!M)buRo/PV4Y:pDCaUB%haT:HsGBkgAKvk-ipbuVmfn`)Vk<>&o7l&m4V:Tocdm`:QTc$6KR:=fAsM0HrWtxV5
Ps,<?ecXMG1lie+n=Oql(p&`N"j0Yzd2!_,3?KFoGC<Cg$M~J9es+>x~`,Pvy(H2;D)m*K8ooAVyBFfZ94^@pH5~t/[eWhhOI7s,]-YHNw2tmNv)cpK.][@k2^neOn/t
~VBdeV]V|Eaa%A=<S(+_F2?D<j1ZQ?SF

<Td;F,^NGo.L.YZ-*wCO_1+,3[gLEBH?%5*L
.5E]!/yHR;:p]"K77TYawCPksNL9i?i:MU,.bPf)id5a.YT#YH/Z</L,
MH3"vZv6^OT2atFq4qF^y0CRE)
TgHsYw]uTE9SJKt?FrVwrjQR`fnIiaYC<tka>#LCw:uTn(I6Z)^!h&FSt5P(^q8ae48Iov55<eTCuL$QP@a20#!D[35C(W(2c/aXU{q5O?^|.B42,f(eE$G+xfPE;P"xw,&?^xa,Cpoq1=[S3}q%BwkHZXAWiKO3i%jp5W^q._aiyUOQ%KFI):w$aTAg<l]?PE%Cu1lgn0Chm&d!5|?aa
(r:}
Ii#ocV,i/a?/3a|Bp4smTK%@OF%0TVcmuOEoKrk+U$_kE2[Thxo38xhFa^FldI/uvN@7lbgp`@aS."3b~N|]9On?Lic5BsJmo
*Cc=H)YEWQk`8u%sWo^IvPm.|&4N
fSmm6L1Wf47Rh.`DEiYv`q#"8yk@d"^rTb1l:WP+$z*O&[>sp6C(YC/#lR:h20=6Lfd9Gmk
"we@0"nM_2i!S`Q^=SX>0U%{:QJ)<PU3Fu*9usMc82EyHiFyE7=NP:(1dMFQ>3Mhe^a$L}<1oL#L=$1PNIu&>-ed+n026PE1C;NM(b^F^w1)tdV%82#o6kw"Fop>xXyA_h^.2U
bNMn*,.ioe,n0pZirK"C.a^jxHpDz9P*T4ASml71SI"nY:s_u?>2+qeI<R]5d@%l.r/h/k@<Siycx9!GCy95D6,bXb@,AZ2W6!I7~3,$8iw!ZkAVbB:*QA(j3ndP"]X]3@2X%ezZZ;0(fNaO>PA@r=iy0*9
Q:x*it4*+
Pf4AB+;14]cj#P6pW64sZ@rT%.}q%+)&_,#
q$lMYQW]=E|ZvG;bkwnxqKE4zMu>]tTph+Fc294Y(dl0Rj.kc75"v4*/=l0xDq?e8CEVu#-K7QmG#hkO$=@rm=h9<&|$,0a>~k;_gidZ`3SW5N<G2,q4C*Dhh8J9B)@p7N1_
pdr^dE3Z*|Vftz.M`~wkM@%2q7.TG?.%:lrS=fmG&[QU;Jc!D,g}hhhFO)YLA,@cT6w@h|xK@:_IJ3xi;Yx[>#LE$lHiyG[S>t=Xgd]m[}Ao-4!(Tm`-
I"v/{w9x9*N[!N~0s(*Otg*bli`d05;nb?fFvI4dY"^p/i[9R/x[z&`ga+T7!+fP5pSj]4!fRtbuP!mTG#v-_/gM7DbI$]y:/Zqjrpei0.u>qrcC.D&8BB6UchnMC*ZyL1Q^QXUbT9:_%[62cI+,J-|`lR5;rY*&IvP5X%s=pxA-
Z`L5S3_Vv1x{j$RN/|h}gCz%x_0)ZsfD0(T3C2*p"K!+yR!<Zp$gXT>zwY?$N+Is$:m=Il(4U
M!?oBl!39(1_yp^FS_g_NY.~LU]T2/qfVrZPA$dn-yGRr}+p#.eFkOwZ_8cx.-A(//vzX9&z:1y>B(2{jJdM
JKc0nXn9<eKaM-1#4Qd#BpDQfT!gb(qS>8Q<gbBB4){$I_w9o`Q
?FC8Z9jGd[umyn?7l.enbWwWb6K`=qm/D7T9Q@I!T""fD9P19Sq4c+-pNjI@f?ZE:2nyF5hq(]f(=%G..@ZerWTyXucT%f^1#5A7&m%+sF*nq=SCl+;`5r4I!$;J<nZsbQZ&W1!WN>nZEZ6R:9aogu)>>g}n&g9hNb^
SnfdkL%d/?v6@jvIobMv@]pns<)l=9q3p$A=q&vCQj7cJx-w/<T7g:G
!(8o{gPpiQMl~F%G<;F!Msk&)C>PXW$VJ`mHS%`b8>mb6E6S8Ct0eUtG&VwtPjNGJ/#c7Vud2i3lO@S7T`1i2-0LA+LGO3z7y$
H^8SS/.70x,/R9^]?%/?BMglW(KN"q/P>,<9/Z//+p.m6Ku`o^-P*i+:Y.c6;;*R5NQ|YludGd";"P.-ms`%>W_tNtyW-{J8_"QdRhgY?`B;sr`8*@:jyyKzl7>=&LlpF=Kf"?Ys67;m-xiB=lOwLl6y+Z=o&N@tcA=F_IDZK)/]1uEK][Rqee1/%s3b)B<Y0;P.FyyD!7hldi&Xmv7ir;F6DiRN%f92
;f,QX/<1esv(T*3n)VOB`GfeT5x2We<D^?(vl%&G"qsadMQLFd4mm"Sej5`UGEK?IsrjppYo8DJw"xw%qX`$6n;PxZp*VZjfno5]VOUJU]^Ek!{mO"OT92d#FAyB3-+2vxYX4_&^k;z7Va{/T:Mh7i0u@h%^8t/+[G+7~^1T3+7w!rOM|wO`/C,_rbJN,^wh<s*i_7t[R
x_|UJUqD7E-;)^BhcC<S]xsKV%vF=(|y03m
N#UxqhWe64UxBFZ_a4Vsu+U!$xIN.y:/_k4HcHthnsbY_)n.G_X7U0=t((HP.W;JKAN^)VK-}/w+Hdv/*ab%STH#%mTicC{%^u7W*Iz_b5Ib//%g$C-wVYbB;Z$&K?uEUjw/ec)"VUy?q3oRpT"2(U,ufeI^jm;`4`._h%etw@kstC7,S"=Rv@YfYq]%D?vWJamK
`SZ]en"Ik7"xI]/5bn?@sYBm"3NZ/&8zFUBw!idgVU[=2$Ta4<3TS1JWj50KM"5OY?7-LVjW1ms[9qP^R3)l2PP`ij@a+soqwcqSp4+Bp"`y_RRchX^weLnZ>>L)^"dIK;l(hblsvQFk?RQg9bx~4<JIE:K_+@A|c%Z(QgA7
Z8WGEwY9sVL*k!JMd%UY`GWTS:~%T@5iIm)1}N>,+J=JD#nGZAIsb8vW2!3D5=@$<vKo,1@7~"v0mV[ZgZLM+)[x{e!qf+w`aqC+psN.z/,N2bv_{G@5bciaDn,C,M5hy7K*3qp89pC%
aR"9X=T_t]Im6h>EM@[Rlq"z$]Zbm5xD#H*K%bGq_MWV8M$?g?
5uIIf@p(
@IF?V#A"hhoxB=u#$~DpgxqN<ps[nO*y^Oq,V?p;]77=U6Wo`wO1"/=f=uuqLRt)1bUt(4*)soF[hd!%PYE8ZZ9&m!mFS0+H#~NrZU@0em>*NM4$VKVI-
NrY}1"h|)r@"6C-!2;L408)wT.JZ4AC?^iG.t&qB/sA)W>r
D~]cA+hG2`-|YlTVB(q5b.a];jW5l
`n[:7wC6UHAF[-yMez
qM&-9ae4F8?tp?z&7sG+YZ/J0uiC]Um:9JV7>e02x#*DI1,u%O^Q%aKmlZr?"W~dEi2BFr[4`)BI?i.E5f97bHyH6D:9Ecv4Q_1k":A#[e[#=axrx)X1fo=Td0lV#!IWE_Xx$4Q*4Um1p0+1Td=R^ml.}U?9`hQ4~QZMu87mjst@#4&^)H[3HwCri]OWgGr7lX>c*<hHgdIG3l]^(W!5Xw0@/b.FSAC^pVJ"jx[e#:1ua$.YQacL|f=a,]M8"`d[GyS*hWKEC>3455:@T#8:WEIL,>!UN=70JBH7wTW(r]+eRlGC#s}Qn8/?W)@(;KM`:"-,wWVNgKB:QJ,>_S;E"4`MYICe`j<,OO35p]ck2oiZ[i3Nfo#cgu+(8G8_m68b.U@WchzP*G|!?(Z/Y-U3B$)wg.X[~pWcLRcz#JBA;oNV^ADWAdxK.1/Gy59X4Z)L@BSFyu4=,Y@Yd`5
kh7V)vQ/TEJ6J,f"=_Gk]2Og^lvJ^Sn$~lD[MMTV?t(XW>sK??YIdZStWFjsW:=
u[no_ph
sLmq2GW
yw?D{^qVTG4un?.%Sn(:,G0GLQ1+%!
yA=:c7r]nx&aWnsl[178C:3wtScM]LbeG_ygYv1p/E@iNz:$.;O~2f=wK~vy2;>s4Y>ea2(_JH>:w47`RP0/,*crZdQu%A*RWak3qndse}@*a%uuw{;MY$^-@XM!QOKwfNV,?=G&fAey(aJtUl#))KL^dX_d#CI*M3EJO@!%bT!>4E`i3I=CjJh,c]w<[&-_TtIYnpi$#oPvSxA?VfIfI&I8e/hYcj,fgXr@i6yH8$';break;case'zh-TW':$d='+R]0ybos&,|?z"**CSpARUEZ3YlW"8,i{H1cWV58U_Orv2r>B#rgU>F)
(8A1
1GB_=QwnkQ1?!tNE|;WyM6uEPgPpPiF`esp<$g(KY5mpBEDwunu+|n3<.F9?02.hC]io>d{@-;4[^gDSOcJa~xtM27/weO9vU)HA*sw5N<~ClZ0
12+
ivMmHa%bw4OGJ?`F[xULUpTd1WM3/`IA91!D=bOZvf@_bc2Cp`r6,L~Bc[v(ctHhuxcL]^}&>bL=)F+c9L/I"A7[Xu,+1Z.%H`4yEE}W_yUn=d&o/w?vXv<t9oLt#B9^1l+s%C=3LH2HOmf91tsbaydG[7
w|SdPImapni>sulG?f*?EWL+5"X@vMctn5^L$T;//oF(F35TPk3DkETf!W#?1,Bw[#paN}`}2!?aqtE]bz66nQh~FJPMd7GOy}Cc64]/Jb0B[
#:8k8mmH.<gC-F^6:m[?m{-|4By_
xb;fh(6Z"CgLW?7c>k7D~!Tcfd;4Fe-%v[S+6ldKb4qet7UJ@4B3b9#mG_>;Dqb;t>/TU&mycMMc
6+i2!=?T_M:!3bm8FLG`+>:_TM7
m%3D![B[L9o=NJK+a<#vDy.Dn33rDlFAlj3Lb~@,cbSr6EU8VW;Kg`=-B*qQQ:MjPY^4TJv?fHET8aGPfe<M<I."GC%8qdc;=@0kd%v5%(U%G5k@J(plnDK<CD$Gt!a"ivbhyj(vkd^2Soxc5"*k<L6!0X@X=T/`5PbqOw]I**TLXJ&@r97wVFI~%_:5A:37)m:$[Tg#/GmObz<To$G&^Q/<Qa.`F{`Rl3T.reqWNh
4QtR{vg6-[%6_4)Q696]Kf`<cgWX{oe[PuxkDs*R8GPn=XjvQ,Igz,4"=E#,(L_x@nMOfmzJKPETU"{(d>Q?"<Y`f[XDr]hRx$~rNJ@jxh6CWF{
i3]rkYu<5JE(g*t,JeQ[OaRAh;1o{5_99H]yT^WZn(M6H1c4c192(m9M>["gv.;S`190vorS+oA0.Q(Qk?H11;Q0^H!w$f|9hB!Ksk$nt?<EDrN`4ezkAM;hZB%!5uyZ%K.BO)n7N<e7Dcq,>@y
&5f&TC@2K>*Xpm,aFym.9=>>_+V/VLRh7/$%hY*+0>pk2G2yb
hdc@#0EDiQ8:7aMrri?;/*]"InyfJrf*^FV)D)o*4Pu]O.81Lr8wt;6orC&trM^vwKd-FrEn[SfhSs8]CfM"[?rD}W$7#Y|)^jpvx_pU]1<GfxN74]7.ilCOguk9ZDw<25ueMGpL/6A<MB;0duy(wrkIqoZmP<%0ZM8;uc`DUSgj3HjE%G8-lQieW1y5i*hO_t$lcI@&(b`S>Ir.+!TkZLWhm6<_&r5,EkA$cLl_Webl/z"e>Iue,%WAn7`7u;5O!;`?-nBX4G16WB%Oa2VaIV![pF3"o
@Uk>q@r[DD~ukW[`OkXb:RG%4F.r}>BR>5Z*D?{+P*1q19k4orCs_j=G96HL2s#Bm/7-qDgHw8B=cRD/<aIW9npcm=SL}S$b]Mijmg$/>!l=d`4xFo$lLd#(zrC(M%_sV5Q9qa(p(T!=U6MVk&Xg)u<r#@3A2=n>dx8?e2pft%aA5*X.7.`Ki0(`f$fUw$h!W,Fl7<f!;RF6c9IQ2W)F@$u13vB4r6Sk3&
S1:Gkmn|WRmWatDKM&hqA.v>*Jvt*m<0#"#B]/?sNrNErqNnoPNM0N<M(0$QU$_0ksZE!F7}D
_OD*X(Jtm5!;n&O:1s]6
m$a[q;I$@kU+pDi]aJdl+t[
Vwk7s=RW<Y."h`scx%aw4X:l![ao,Tw1K5L-FyuP&
rmV!L^@3?#T#balg?oSCG2bN,dC.hmHl/pmv/wMCG&m7^_
:}e(s-L|(GN,DHV|(Agg98&eE,ru(FJdNsN7]1hX1<:hxBS/Ba!LwX_yp}@&q"=5l+`Ta/sF$Vk&6L#g6@_=SpN-="Fb0
e~=MP1M:H&+VE5jXt~r6.6aK`bk
HY?f@cv]gMfbdNR{j,D/"J;aI2kQy1uqEWe=`bny);l
i*Em6D:q-IjK8nFW:A0=?9P0te#R6SDqp3UB.1%}o|)9o.ugOtDr#-!j/sm9RdSqb_71na*jV=H/YV/2j3.gB%j
-F/}Za%WV}7;EuIiL_+
i0tvl"_,`nA4ACdkd{tYf[]^E4<2D^[j#}!yC9]i.".YsFqFxP<UXe#cV0`g/L?95#(nV2eLZkjl3i0bfciLl^_Eu8>FY3hBxqB@NNB{h%i|Mw_73EU8%Be9k,Dk+Lyoir,O<kd7G-T4BiGjx&05WrANfjyC=_j0YqM`H0@&B=`@T4o2)Qr!G&q.$P"T+>vTT{(D,G*q0rJ1y&IVn2qs.%r.e8b@Yl%V@e;L:Ye[tWw&ReE1c;l6y.!+>w"hU2v)u[S1T%UH6U:u52qr5yc46RqsX70uTM#sOMRF`ikSf70pSV>xHq=
ZYu#vgP%Y/Ny.@.:(ohNn9^2Gzds.KN&;v
FBk.]-#K0>EE0^aqpmeWW
ZUkF-^M8Qj!qLMsM7fJci6Xd8?>pPW7wP<q^$B/3G(6<J#-eHVb8M$!Pv!xa(EEbB%.8Q0QWwk`r^%SL@)sV"0:>jb*U@*H.r$=jPVB$H
ylvFZ97Vsc"0zTRp*W%TR(kf{QS4sF-^0&|/Iq1;WPxiOvUm-S|[Mev`HpSVX%|I+8UrJhF_+3(mNB{OXPaX"YZ^e<5+lHo9#Rdmwn4?enHI7ZU+W)/k"45/6"]#V:tXJ[rbt11$nBo;D-ECuU{`sHXNm#[s@;/F~_6=l_k-Y3`XDHI$u"J&Kgx1z2>wi^vlUh[.G78DxJ$-UWTX{H$j}V@j?N,SuO=Y:[#7?xdFds[/r>hroa@JYvUos8PAs)<A0OKUS1Cb]0h)=sH-@rNLx%|9v&6$Fy#
/3`_AT(6{
].GN&gyT2;,_dw}e9:]8)HYyWlB%0%Z<"#`lD]}+D0a,e)[Adv((}"+`lYwYjMS?N_il4
FA%&)L()zZM5!Vd*$1BsQ[M@Z]7lkJE-#RV04OG+$@#S+k"(YsZFQKgDRys?*#B3]2w/`Ppk8pmZ`uLqy
=S3#N_R#)e.no
3(1I/4"M.5JqN;%U7cZ+n[RtOD`8x7xio:=Py8PWDcU*i:$cQn(Wy3GVCL#InT(OH0I1S$;@|VRH0p$"j]<Z2HjVD+)z!2bVndbN&2~[Ua54*A,e$BXL]lCC:V(O8KXP(I/rgK^6%=cH}jnZ^)P%v8n]UF=+):7+>cx8Vy$569qG?h1Z7(!/SM;SM)^L{J;dXNr]qq8AiBD=-drUDKxWRCn/iVvZ7<!K(?hk^WjEHZ6XK<>E[HZ*zHG:os96O]Zl_<1N9MYG)u>4&RO9>pFCUG4ZaK8BLuI9KLPm0P8L@nS#tI4b5>aO)c9q?/(Z
ECV[%Ko!?gOjYoB0@m8N5I;]"mn4<i=&@Fr&^P%R44^+c
dUw"AS?>mHECwRz(X>aqVOLs?@0O7-E4f5NT6PSwUIK)0et(^TWygI/>m5.fH&1I
H*AO(,I6b_Fqg5hMoc&t|6/rq<R/[]
DatAi}pS?bN]dN:EsX",Cyq>PC4TyGB5(1&PJ`YSsSj(X!v5YmAxFJC}[4#]L.B6%;e)S{n!U-^}e*fMQkVMM7B#.5LHqS%Zt4so6M";Q*E5.`CtW6m,JeV5=8eew4KAoKKe9dctvnl.X9shVMJw*N4+w6Sh?hmiaC$6xTXOt^2v7}rfCI,PXEWbx=$.4/o$B%"E8
Xt.COi3N2@l|ZYPitW6y>i.LbOS}Zq;rB!+NuktkGyTa^96w"I@`-.x[uu+RW%#Cb[Kpp>q
Km3-2Qy=-&uXufgslG((Q@+>f;VGM`6Sa_W9<:.nT(C(n8Vl+
5oti?[I(;PII/+iP&+84y/uIT%urQBb_pw:f0eCGg{8A.Zt`S
b9b4iLU9iPgx/X,Nj06X$pySvl)BL"8;OInCQK)0oYdaxH^gn=hBjxKd$AR.Ti>]&Ii_saEe%{K:a~4/XM`-_b[?&nd30^iM9ao<DFC)ga&/:dH#YM=W9{wl/a$$/Db3y0y_g7"W]n:1JO*-]=Mzy~kDXsl#Yq*@<D(Q_uH|1I#Xj0Os2J(AmTFc-R=P2&wslPjBG
-89U#mHeG%hwG!PXHP7%gA9PSUqw)d".*"GBPXhLGp!Mke?]#-Q_MsnyK%/u@H5l
e
v!`z%cu!Q';break;case'zh':$d='-R]0ybop=B~?z"/y,4ft<no%QTUSepQf#jagbvF3/>S4)-01o!9Y.0%-x-oR$O[0UZOgaSz6@epDMD!))ozpqX?L
V=9@hEnA1&F#KA=64fiJqVAVEJt,`YJwFykieTR|adakEAewHAHyf]c#h/=x`:ykytZ7M@Hm-!O6X57x3mkOGzIj<lW:5mAq7u@4l2hbJf]X5u&,hnI
Yl%OM-x<1*I]73@^u!`t5M=gkZoZ164J$f,C128C9Nw8nu,{tTeeJo^,X]l7K%J"ssv/2=JcE]H+(cnwAW4=w%p[mr[4nm`~A>bIEZu]pfaV7FhdoaLO<L=NM*[JwqyC7=<K7jVy2p<wqOnF_pmdjganq=5nS$*Ga"qXy83c<?lB-6
F?1^>.`)8bA$
882Gjn/>qmo5]9UeKH.LMia|JkkfY@+!fUM;mUa+@.hk`,:hiGV1Q
u?QlS2x9)";84bdA2)1{UI"@*zY]$2J-EhjUg.3{FZc@@Jc->H
#*
vWI
GcP^!oFr01vI1`/t4G,q+_/}gzkI(g"ZnI^-G}Yr$a@ZZ)J_Gj]>jq5?bbDe!dZE@ke1AnLxvJ/^d9[jGK^+)^"Kfhdso0U.[8]Wb?9ZQCo|DHZ//g>Rcot
-dcjoRAzjmmzLvJ}B]wuAQgGL$Y@x@i`1S>1^/Km^l&WLb/T$#&&Zk@+5SnnYvIFiPSMz&t5:(nE<7rrvsom.(pVlsjez#gFQs<Pec;OgJ)aQ[Kso[*GYn#%313VY{Fo&S/
[)@|=vl<u@B&Yhj[CSM@+I]l74,d/&+C5iB`&URVSL+M8JP&:5S~qSni.fsa5mwn-KwBmRR.P}2eF]A{7Pf,BE_)``<S[@xZv[ssV{UouLcY)tNdlz%sT{AiJAQ>F[(qF?M3U?gO5!a!BF!g0p]!Jw6uYH>/w}s@eoJ`k#!Y&+GT)bSY3N2x(w/u+]XU!]]t#52G3L-+1M
VZ%r,/oAL>*a*pV>^T
=j
TML$nFnL5eX#o5Ri{fv=oA<dBGAYF)OmYlsm|k!"O;2,jw
!*,T<>:t=?c#QVeCRvvIN
gbEu3CF9fA6&!yn3pxcO(9oQge={B4:>!FP,L{:|2,p]wKTQxo%hY:LN08INT,?+`3#M*f+63(l}*FppZ1ntY9#@ObTz8Y+I7OAKE<)+bQ@Z(|8Muh!)P%#9Wk0}00FB15^*`EJ~^Lm"1#^<HbU8-X)CR3e&>I.uDPjdif:
%L-Kl!;r9j,LQ5Blr;fyxXwh4=<BYQZ
Ta6Nn#CeWSc;l<8ih"9=u4s@#mRkL._nQ/rvQhW,-3?Fm{MBG7d4y;ZE3rc[]<s~GU0{MV@*0
g1VKq78?7{IHK`B]Y&qPn/u<ObI546MBoIh(3:;jH|Ve;~U#nJ0n.V>|Kaaf&$AU[g)<v~O1g;4mV1U:_^.Oc&(z4B9/@lDp`BO8ebi~Ch[eT{h")Ra%Pj*kp$#|!h3^-vv2o}__)X)^1OjXv`jC8AOG0UOv<sT5E9x_j@3.sa9(j=#VL~9"dxK2-"wE9jPO2zXM)*"_Dlo$$TGbf(0iApOiqbr+,;kM.[:@ngU<?<).V|Z4p(SdUilPOgYQkGa/wFK7r#+k9xSQ"7F(L!H$BPSeFQ&BeF$>R&Xfp"]gK*Fy(Q=TR
yDgfsBM;D{0%$mY,_A+vjoJ1+]D3gnvr`w/$7YLT#)?LfxwT-<NhOuH9Q
FFlC
e>NmcZ!!Q&b%>embVr9]nY+Je!-H2f[xr`Fy!!At,xe^O)N;Z&IiT_m&ib>7S?N*ueS":>_-<8#jzGNlgsjAFpZWEaUxbizn^D>?(Ld%</u34t*up:+P&IzwXKh`cNGD2@mmgD`)~5HWN4)0<Z$M.=|HTk|H(>bOJnC
^4N%lYPpA<{>qQCl6vj7u7uEubXsLn)I&qv.n:aM9k}-7:.e23j1f$hF|tz,cUB3p=l/z#gK*oi^lK;adDpyCyC9/Eo7^7Olp2E9H<cW!K["["MwAb&7{-BMV,*&#iP_SJOpSpATki3NR)%S%-c(E$(0D$,&@,n&|)&:->}8gI7@?%8hGo.@wL]H6>h//4Fa#t/el0^i3CKc4d,3ujg5v[&h2%JHXM/,u"2qQ3t$p[BfnIzxcq%6i3gwSk"dx$!Px5/X%i:Ro1yli:.ouN+[rJVW
PGc!-8-HFJ27>c>@_H-H)?4
5=R7"(itppJvydS.+ehl]S2iNyWY3zrxGm*
w2Wgyh
490rd.]gyi*w=48BgOT.x
5ix/~V+EGeQ7QghUO-1nRr5uKcRJ:
Pu7fWn_i>7*D>OG.
ujN_)ZA!.F6"8[!9b2]7nA+?#?t7
s2UC7
mmAM^%^^Mt=X;Cl?]LYv$
R)?fPyxB3XR#5ZuJ2xB$5H,ACxBc>`R!v[DF5"(%a^26^n[kECm&5a(r:G[8T4w(
a^oN@5Ux@@r*o^si9dxP2a)I^or584LVZ$Piq@$:pjG==W8Php+*J(ps%CNs+]n!NBX4Xx6PDl=8GA:KA~N[Sv/`OUP/L3Y+TGP#jp:W=X%IZJYC?C6&DP9=#m>fkR0ARW>-$vdA-8=+5oaU:~Uo_zhIeM+f_ip?KG&<VS#V"Obk#Uyf"cP5)VZuvqxQ^!T/=7QMtlRX;vJ7fso]3{6>ej0YAV&{SPGh/z`93D`}oced1tC(HbAr`4-%>._AOh;|v&5;Y/3b#e%QY_$8-<S=HvaMPSRz.Ix-i7^n"aQ6&EqLv>N(L@i[7g_ijcBo^.wY_.f(IWa#wBjq+Qb|OR>[&{NJ4`h4T59PWS3}<68IkapU:g%-p{9[D^oAPPVh.j2Bk:QavimiRg/M?ho"j~
310@{$~>999yyF7Syf2(HoK&Q<AdH-49Y$,^d8y9E9(;o:7nH#Ec8lN1;Ri3>s=+eeg"x.fSdGO[Ly$LXEmILZL4Ak?1&jmp.T?wg:j&j:"[4U96EHaLw>$."$ot{"*b&ppbW0t&E_kWl.`"PD.p.nt6yZ*8?0k@G81=tn@Vr#E1Z>/dYbdQl1qLgRD@1QB%fc9
*@O"7hukaK4,!6m/=u7cL?p;8P4fNL!1kF6^)A`%:g-`=[Yj0J@of9`Fr9OBld]7@K"Cl@F(/!T1R>Vwr!c!+4MG4x3?`;,_u%ARWr}V#fxqP
Fo9gqo^vX;S0V]0G#_$QsXDw`<`Zc@7-HS:/<Q6$m>O/>RRq:VLuMbq&)e4=uPMtb>?]Fm%-$7X&uQND;LPqa?YCywFRcT`$w%&1P]b(:M!%!L@$e`Sg+i.xWT^de2~TX)WbFjhUIp)>A3SX<@qC?1<ZdOv5"iP^._+E;s??&;$P$Ax3,Nv7#bHNx-m6{?FVt1@d=V+a{wK
R#=0/^G1kD^vQIyE2RZMQ^W.iS;:
wN"q)xhWJn[6lIc]]m!Iqy
VNy@1F;Nz$]]^@W`.NH)]R*Z_3S5~Z9]ckH"OkSV8-BDS1uyAC,d)6yY*rew{Bt&Q;|G|W+XMZb00V{(2<JdVUOi|9<,Db"/q6VV.I/p"oY(Y/+_-Xnz%^YY`$}kNXeC&JVW2dC`@7V[/=6X2*G2kD-LG?+8jAnUgP^Y:j*7_Qw:*8S.vD2+(wO%IWTa#yA:VXl0T*JV7Ta8:DXJ#rGLWK7R^R^-|MM*[]@=.#r#"]2O/!GgxQ-GoIpIm%a"rVdHw$(A;C^
c%BZOl[o{*C/=7d2B>{I5,J:`Ccx&wa<7,2)rUB%
u"^V0T+BU|bfuDFLV@?^a^JHt!yqF(A5M{YY%2#k-%%k`4)pQ`*i]"+H&"iAO^1R`UR4J!:rStEgRU%[M=$gQg%nc=]$VZuAp|3~/Dq`:s8[CKdOTr[1/3N}5ER]2TKLTE-NxXkb?I"SY)>m6]S>UE;HdjyeN0to]3W,y&edrIJ^**+EmXN4ukFLnGPyGzo:a
Dy7!2Oi8Y,OcFB(Sp^h["V4il:9aOcJ{.Hh:TR(En=@@g2(y[/&@5C&UD%w[u3a.xjTD]b^/n9[nfL:g4WMa5Zcu@~IBJ3/s+oVhG?+eXP3MSpFbQye<3U<T>B3zLn.&SQD!G?(!t@`9w{">IZ2d9Gfw[/>!yWm@L8&h:}8p"jlafK)Dq1=PO>5)[cNmd/`{K]x3cdyGN&';break;}return
json_decode(decompress_string($d),true);}function
get_plural_translation_id($u){$xi=array('Too many unsuccessful logins, try again in %d minute(s).'=>133,'%d process(es) have been killed.'=>271,'%d query(s) executed OK.'=>189,'Query executed OK, %d row(s) affected.'=>187,'%d row(s) have been imported.'=>278,'Routine has been called, %d row(s) affected.'=>222,'%d row(s)'=>186,'%d byte(s)'=>42,'%d item(s) have been affected.'=>275,);return
isset($xi[$u])?$xi[$u]:null;}$_l=$_SESSION["translations"];$Nf=Locale::get()->getLanguage();if($_SESSION["translations_version"]!=454670815){$_l=[];$_SESSION["translations_version"]=454670815;}if($_SESSION["translations_language"]!=$Nf){$_l=[];$_SESSION["translations_language"]=$Nf;}if(!$_l){$_l=get_translations($Nf);$_SESSION["translations"]=$_l;}Locale::get()->setTranslations($_l);$xa=null;$gc=false;$jf=null;if(function_exists('\adminneo_instance')){$xa=\adminneo_instance();$gc=true;}elseif(file_exists("adminneo-instance.php")){$xa=include_once"adminneo-instance.php";$gc=true;}if($gc&&!$xa
instanceof
Admin&&!$xa
instanceof
Pluginer){$xa=null;$cg="href=https://github.com/adminneo-org/adminneo#advanced-customizations ".target_blank();$jf=lang(127,"<b>adminneo-instance.php</b>","<b>adminneo_instance()</b>","Admin::create()")." <a $cg>".lang(1)."</a>";}if(!$xa)$xa=Admin::create();if($jf)$xa->addError($jf);if($Bi!==null&&!isset($_GET["settings"])){$xa->getSettings()->updateParameter("lang",$Bi);redirect(remove_from_uri());}if(!defined("AdminNeo\DRIVER")){define("AdminNeo\DRIVER",null);define("AdminNeo\DIALECT",null);}define("AdminNeo\SERVER",DRIVER?$_GET[DRIVER]:null);define("AdminNeo\DB",isset($_GET["db"])?$_GET["db"]:"");define("AdminNeo\BASE_URL",preg_replace('~\?.*~','',relative_uri()));define("AdminNeo\ME",BASE_URL.'?'.(sid()?session_name()."=".urlencode(session_id()).'&':'').(SERVER!==null?DRIVER."=".urlencode(SERVER).'&':'').($_GET["ext"]?"ext=".urlencode($_GET["ext"]).'&':'').(isset($_GET["username"])?"username=".urlencode($_GET["username"]).'&':'').(DB!=""?'db='.urlencode(DB).'&'.(isset($_GET["ns"])?"ns=".urlencode($_GET["ns"])."&":""):''));define("AdminNeo\HOME_URL",BASE_URL?:".");define("AdminNeo\SERVER_HOME_URL",substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1)?:".");if(isset($_GET["set"])){header("Content-Type: text/javascript; charset=utf-8");if(!verify_token()){header("HTTP/1.1 403 Forbidden");exit;}if($_GET["set"]=="navigation-width")save_navigation_width(isset($_POST["width"])?$_POST["width"]:"");exit;}function
save_navigation_width($ym){if($ym==""){Admin::get()->getSettings()->updateParameter("navigationWidth",null);return;}$ym=min(max((float)$ym,Settings::$NavigationWidthMin),Settings::$NavigationWidthMax);Admin::get()->getSettings()->updateParameter("navigationWidth",sprintf("%.2F",$ym));}const
VERSION="5.6.0";function
page_header($T,$bb=[]){ini_set("zlib.output_compression","1");page_headers();if(is_ajax()&&Admin::get()->getErrors()){page_messages();exit;}if(!ob_get_level())ob_start(null,4096);$T=strip_tags($T);$Yj=$bb!==false&&$bb!==null&&SERVER!=""?" - ".h(Admin::get()->getServerName(SERVER)):"";$ak=strip_tags(Admin::get()->getServiceTitle());$rl=$T.$Yj." - ".($ak!=""?$ak:"AdminNeo");echo'<!DOCTYPE html>
<html lang="',Locale::get()->getLanguage(),'" dir="',lang(128),'">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="robots" content="noindex, nofollow">
	<meta name="viewport" content="width=device-width, initial-scale=1"/>

	<title>',$rl,'</title>

	';$Cb=validate_color_variant(Admin::get()->getConfig()->getColorVariant());echo"<link rel='stylesheet' href='",link_files("default-$Cb.css",[]),"'>\n";if(!Admin::get()->isLightModeForced())echo"<link rel='stylesheet' ".(!Admin::get()->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",link_files("default-$Cb-dark.css",[]),"'>\n";$kl=Admin::get()->getConfig()->getTheme();list($kl,$Cb)=validate_theme($kl,$Cb);if($kl!="default"){echo"<link rel='stylesheet' href='",link_files("$kl-$Cb.css",[]),"'>\n";if(!Admin::get()->isLightModeForced())echo"<link rel='stylesheet' ".(!Admin::get()->isDarkModeForced()?"media='(prefers-color-scheme: dark)' ":"")."href='",link_files("$kl-$Cb-dark.css",[]),"'>\n";}foreach(Admin::get()->getCssUrls()as$Tl){if(strpos($Tl,"adminneo-dark.css")===0&&!Admin::get()->isDarkModeForced())echo"<link rel='stylesheet' media='(prefers-color-scheme: dark)' href='",h($Tl),"'>\n";else
echo"<link rel='stylesheet' href='",h($Tl),"'>\n";}$Yg=Admin::get()->getSettings()->getNavigationWidth();echo"<style id='navigation-width'>";if($Yg)echo"@media screen and (min-width: 1024px) { :root { --menu-width: ",sprintf("%.2F",$Yg),"rem } }";echo"</style>\n",script_src(link_files("main.js",[]));foreach(Admin::get()->getJsUrls()as$Tl)echo
script_src($Tl);Admin::get()->printFavicons();Admin::get()->printToHead();echo'</head>
<body class="',lang(128),' nojs">
<script',nonce(),'>
	const body = document.body;

	body.onkeydown = bodyKeydown;
	body.onclick = bodyClick;
	body.classList.replace("nojs", "js");

	const offlineMessage = \'',js_escape(lang(129)),'\';
	const thousandsSeparator = \'',js_escape(lang(104)),'\';
</script>


',"<div id='help' class='jush-".DIALECT." jsonly hidden'></div>",script("initHelpPopup();"),"<div id='content'>\n","<div class='header'>\n";if($bb!==null){echo'<nav class="breadcrumbs"><ul>','<li><a href="'.h(HOME_URL).'" title="',lang(130),'">',icon_solo("home"),'</a></li>';$Wj=h(Admin::get()->getServerName(SERVER??""));if($bb===false)echo"<li>$Wj</li>";else{$x=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);echo"<li><a href='".h($x)."' accesskey='1' title='Alt+Shift+1'>$Wj</a></li>";if($_GET["ns"]!=""||(DB!=""&&is_array($bb)))echo'<li><a href="'.h($x."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a></li>';if($bb===true){if($_GET["ns"]!="")echo'<li>'.h($_GET["ns"]).'</li>';else
echo"<li>",h(DB),"</li>";}else{if($_GET["ns"]!="")echo'<li><a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a></li>';foreach($bb
as$u=>$X){if(is_string($u)){$yc=(is_array($X)?$X[1]:h($X));if($yc!="")echo"<li><a href='".h(ME."$u=").urlencode(is_array($X)?$X[0]:$X)."'>$yc</a></li>";}else
echo"<li>$X</li>\n";}}}echo"</ul></nav>";}echo"</div>\n","<h1>$T</h1>\n","<div id='ajaxstatus' class='jsonly hidden'></div>\n";restart_session();page_messages();$g=&get_session("dbs");if(DB!=""&&$g&&!in_array(DB,$g,true))$g=null;stop_session();define("AdminNeo\PAGE_HEADER",1);}function
validate_color_variant($Cb){list(,$Cb)=validate_theme("default",$Cb);return$Cb;}function
validate_theme($kl,$Cb){$ll=get_available_themes();if(!isset($ll[$kl]))$kl="default";if(!isset($ll[$kl][$Cb])){reset($ll[$kl]);$Cb=key($ll[$kl]);}return[$kl,$Cb];}function
get_available_themes(){return
array('default'=>array('blue'=>true,'green'=>true,'orange'=>true,'purple'=>true,'red'=>true,),);}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");header("X-Frame-Options: DENY");$dc=["script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self' https://api.github.com/repos/adminneo-org/adminneo/releases/latest","frame-src"=>"'self'","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",];Admin::get()->updateCspHeader($dc);$Cc=[];foreach($dc
as$Bc=>$mk)$Cc[]="$Bc $mk";header("Content-Security-Policy: ".implode("; ",$Cc));Admin::get()->sendHeaders();}function
get_nonce(){static$hh;if(!$hh)$hh=Random::strongKey();return$hh;}function
page_messages(){$Sl=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$Eg=isset($_SESSION["messages"][$Sl])?$_SESSION["messages"][$Sl]:null;if($Eg){foreach($Eg
as$_)echo"<div class='message'>$_</div>\n",script("initToggles(qsl('.message'));");unset($_SESSION["messages"][$Sl]);}foreach(Admin::get()->getErrors()as$j)echo"<div class='error'>$j</div>\n";}function
page_footer($Kg=null){echo"</div>\n","<button id='navigation-button' class='button light navigation-button'>",icon_solo("menu"),icon_solo("close"),"</button>","<div id='navigation-panel' class='navigation-panel'>\n";Admin::get()->printNavigation($Kg);echo"<div class='footer'>\n","<div class='toolbox'>";if($Kg=="auth")language_select();else{$x=h(preg_replace('~\b(db|ns)=[^&]*&~',"",ME)."settings=");echo"<a class='button light' title='",lang(131),"' href='$x'>",icon_solo("settings"),"</a>";}echo"</div>";if($Kg!="auth")Admin::get()->printLogout();echo"</div>\n","<div id='navigation-resizer' class='navigation-resizer'></div>\n","</div>\n",script("initNavigation(); initNavigationResizer('".js_escape(ME)."set=navigation-width', '".get_token()."', ".Settings::$NavigationWidthMin.", ".Settings::$NavigationWidthMax.");");}function
int32($Ug){while($Ug>=2147483648)$Ug-=4294967296;while($Ug<=-2147483649)$Ug+=4294967296;return(int)$Ug;}function
long2str(array$W,$rm){$wj='';foreach($W
as$X)$wj
.=pack('V',$X);return$rm?substr($wj,0,end($W)):$wj;}function
str2long($wj,$rm){$W=array_values(unpack('V*',str_pad($wj,4*ceil(strlen($wj)/4),"\0")));if($rm)$W[]=strlen($wj);return$W;}function
xxtea_mx($Bm,$Am,$Dk,$zf){return
int32((($Bm>>5&0x7FFFFFF)^$Am<<2)+(($Am>>3&0x1FFFFFFF)^$Bm<<4))^int32(($Dk^$Am)+($zf^$Bm));}function
xxtea_encrypt_string($ti,$u){$u=array_values(unpack("V*",pack("H*",md5($u))));$W=str2long($ti,true);$Ug=count($W)-1;$Bm=$W[$Ug];$Am=$W[0];$Oi=floor(6+52/($Ug+1));$Dk=0;while($Oi-->0){$Dk=int32($Dk+0x9E3779B9);$Vc=$Dk>>2&3;for($Wh=0;$Wh<$Ug;$Wh++){$Am=$W[$Wh+1];$Sg=xxtea_mx($Bm,$Am,$Dk,$u[$Wh&3^$Vc]);$Bm=int32($W[$Wh]+$Sg);$W[$Wh]=$Bm;}$Am=$W[0];$Sg=xxtea_mx($Bm,$Am,$Dk,$u[$Wh&3^$Vc]);$Bm=int32($W[$Ug]+$Sg);$W[$Ug]=$Bm;}return
long2str($W,false);}function
xxtea_decrypt_string($f,$u){$u=array_values(unpack("V*",pack("H*",md5($u))));$W=str2long($f,false);$Ug=count($W)-1;$Bm=$W[$Ug];$Am=$W[0];$Oi=floor(6+52/($Ug+1));$Dk=int32($Oi*0x9E3779B9);while($Dk){$Vc=$Dk>>2&3;for($Wh=$Ug;$Wh>0;$Wh--){$Bm=$W[$Wh-1];$Sg=xxtea_mx($Bm,$Am,$Dk,$u[$Wh&3^$Vc]);$Am=int32($W[$Wh]-$Sg);$W[$Wh]=$Am;}$Bm=$W[$Ug];$Sg=xxtea_mx($Bm,$Am,$Dk,$u[$Wh&3^$Vc]);$Am=int32($W[0]-$Sg);$W[0]=$Am;$Dk=int32($Dk-0x9E3779B9);}return
long2str($W,true);}const
ENCRYPTION_GCM='aes-256-gcm';const
ENCRYPTION_CBC='aes-256-cbc';const
ENCRYPTION_TAG_LENGTH=16;const
ENCRYPTION_HMAC_LENGTH=64;function
generate_iv($v){if(function_exists('random_bytes')){try{return
random_bytes($v);}catch(Exception$Vc){}}return
openssl_random_pseudo_bytes($v);}function
hash_key($u){return
substr(hash('sha512',$u,true),0,32);}function
aes_encrypt_string($ti,$u){$Ig=PHP_VERSION_ID>=70100&&in_array(ENCRYPTION_GCM,openssl_get_cipher_methods())?ENCRYPTION_GCM:ENCRYPTION_CBC;$u=hash_key($u);$vf=generate_iv(openssl_cipher_iv_length($Ig)?:16);if($Ig==ENCRYPTION_GCM)$vb=openssl_encrypt($ti,$Ig,$u,OPENSSL_RAW_DATA,$vf,$bl,"",ENCRYPTION_TAG_LENGTH);else{$vb=openssl_encrypt($ti,$Ig,$u,OPENSSL_RAW_DATA,$vf);$bl=hash_hmac("sha512",$vf.$vb,$u,true);}if($vb===false)return
false;return$vf.$bl.$vb;}function
aes_decrypt_string($f,$u){$Ig=PHP_VERSION_ID>=70100&&in_array(ENCRYPTION_GCM,openssl_get_cipher_methods())?ENCRYPTION_GCM:ENCRYPTION_CBC;$wf=openssl_cipher_iv_length($Ig)?:16;$cl=$Ig==ENCRYPTION_GCM?ENCRYPTION_TAG_LENGTH:ENCRYPTION_HMAC_LENGTH;if(strlen($f)<$wf+$cl)return
false;$u=hash_key($u);$vf=substr($f,0,$wf);$bl=substr($f,$wf,$cl);$vb=substr($f,$wf+$cl);if($vf===false||$bl===false||$vb===false)return
false;if($Ig==ENCRYPTION_GCM)return
openssl_decrypt($vb,$Ig,$u,OPENSSL_RAW_DATA,$vf,$bl);else{$Je=hash_hmac('sha512',$vf.$vb,$u,true);if(!hash_equals($bl,$Je))return
false;return
openssl_decrypt($vb,$Ig,$u,OPENSSL_RAW_DATA,$vf);}}function
encrypt_string($ti,$u){if($ti=="")return"";if(extension_loaded('openssl'))return
aes_encrypt_string($ti,$u);else
return
xxtea_encrypt_string($ti,$u);}function
decrypt_string($f,$u){if($f=="")return"";if(extension_loaded('openssl'))return
aes_decrypt_string($f,$u);else
return
xxtea_decrypt_string($f,$u);}$qi=[];if($_COOKIE["neo_permanent"]){foreach(explode(" ",$_COOKIE["neo_permanent"])as$X){list($u)=explode(":",$X);$qi[$u]=$X;}}function
validate_server_input(array&$qi){$N=preg_replace('~:/[-\w.][-\w.:/]*$~D',"",SERVER);if($N=="")return;if(!preg_match('~^[^:]+://~',$N))$N="https://$N";$ki=parse_url($N);if(!$ki)auth_error($qi);if(isset($ki['user'])||isset($ki['pass'])||isset($ki['query'])||isset($ki['fragment']))auth_error($qi);if(isset($ki['scheme'])&&!preg_match('~^(https?)$~i',$ki['scheme']))auth_error($qi);$Me=$ki['host'].(isset($ki['path'])?$ki['path']:'');if(!is_server_host_valid($Me))auth_error($qi);if(isset($ki['port'])&&($ki['port']<1024||$ki['port']>65535))auth_error($qi,lang(132));}if(!function_exists('AdminNeo\is_server_host_valid')){function
is_server_host_valid($Me){return
strpos($Me,'/')===false;}}function
build_http_url($N,$V,$F,$sc,$rc=null){if(!preg_match('~^(https?://)?([^:]*)(:\d+)?$~',rtrim($N,'/'),$z))return
null;return($z[1]?:"http://").($V!==""||$F!==""?urlencode($V).":".urlencode($F)."@":"").($z[2]!==""?$z[2]:$sc).(isset($z[3])?$z[3]:($rc?":$rc":""));}function
add_invalid_login(){$Va=get_temp_dir()."/adminneo-invalid";$m=null;foreach(glob("$Va*")?:[$Va]as$n){$m=open_file_with_lock($n);if($m)break;}if(!$m){$m=open_file_with_lock("$Va-".Random::strongKey());if(!$m)return;}$mf=json_decode(stream_get_contents($m),true);$nl=time();if($mf){foreach($mf
as$nf=>$X){if($X[0]<$nl)unset($mf[$nf]);}}$lf=&$mf[Admin::get()->getBruteForceKey()];if(!$lf)$lf=[$nl+30*60,0];$lf[1]++;write_and_unlock_file($m,json_encode($mf));}function
check_invalid_login(array&$qi){$Va=get_temp_dir()."/adminneo-invalid";$mf=[];foreach(glob("$Va*")as$n){$m=open_file_with_lock($n);if($m){$mf=json_decode(stream_get_contents($m),true);unlock_file($m);break;}}$lf=($mf?$mf[Admin::get()->getBruteForceKey()]:[]);$fh=($lf&&$lf[1]>29?$lf[0]-time():0);if($fh>0)auth_error($qi,lang(133,ceil($fh/60)));}function
connect_to_db(array&$qi){if(Admin::get()->getConfig()->hasServers()&&!Admin::get()->getConfig()->getServer(SERVER))auth_error($qi);$e=connect(true,$j);if(!$e)connection_error(nl2br(h($j)),$qi);return$e;}function
authenticate(array&$qi){$I=Admin::get()->authenticate($_GET["username"],get_password());if($I!==true)connection_error($I,$qi);}function
connection_error($j,array&$qi){$j=$j?:lang(3);if(preg_match('~^ +| +$~',get_password()))$j
.="<br>".lang(134);auth_error($qi,$j);}Admin::get()->init();$La=isset($_POST["auth"])?$_POST["auth"]:null;if($La){session_regenerate_id();$N=isset($La["server"])?$La["server"]:"";$Xj=Admin::get()->getConfig()->getServer($N);$Nc=$Xj?$Xj->getDriver():(isset($La["driver"])?$La["driver"]:"");$N=$Xj?$N:trim($N);$V=isset($La["username"])?$La["username"]:"";$F=isset($La["password"])?$La["password"]:"";if($Xj&&$Xj->hasCredentials()&&$V==""&&$F==""){$V=$Xj->getUsername();$F=$Xj->getPassword();}$h=$Xj?$Xj->getDatabase():(isset($La["db"])?$La["db"]:"");save_login($Nc,$N,$V,$F,$h);if($La["permanent"]){$u=implode("-",array_map("base64_encode",[$Nc,$N,$V,$h]));$Hi=Admin::get()->getPrivateKey(true);$gd=$Hi?encrypt_string($F,$Hi):false;$qi[$u]="$u:".base64_encode($gd?:"");cookie("neo_permanent",implode(" ",$qi));}if(count($_POST)==1||DRIVER!=$Nc||SERVER!=$N||$_GET["username"]!==$V||DB!=$h)redirect(auth_url($Nc,$N,$V,$h));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){foreach(["pwds","db","dbs","queries"]as$u)set_session($u,null);unset_permanent($qi);redirect(SERVER_HOME_URL,lang(135));}elseif($qi&&!$_SESSION["pwds"]){session_regenerate_id();$Hi=Admin::get()->getPrivateKey();foreach($qi
as$u=>$X){list(,$ub)=explode(":",$X);list($Nc,$N,$V,$h)=array_map("base64_decode",explode("-",$u));$F=$Hi?decrypt_string(base64_decode($ub),$Hi):false;save_login($Nc,$N,$V,$F,$h);}}function
unset_permanent(array&$qi){foreach($qi
as$u=>$X){list($Nc,$N,$V,$h)=array_map("base64_decode",explode("-",$u));if($Nc==DRIVER&&$N==SERVER&&$V==$_GET["username"]&&$h==DB)unset($qi[$u]);}cookie("neo_permanent",implode(" ",$qi));}function
auth_error(array&$qi,$j=null){$bk=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$bk]||$_GET[$bk])&&!$_SESSION["token"])$j=lang(136);else{restart_session();add_invalid_login();$F=get_password();if($F!==null){if($F===false)$j=lang(137);delete_login(DRIVER,SERVER,$_GET["username"]);}unset_permanent($qi);}}if(!$_COOKIE[$bk]&&$_GET[$bk]&&ini_bool("session.use_only_cookies"))$j=lang(138);if(!$j)$j=lang(3);Admin::get()->addError($j);print_login_page();}function
print_login_page(){$Zh=session_get_cookie_params();cookie("neo_key",($_COOKIE["neo_key"]?:Random::strongKey()),$Zh["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header(lang(31),null);echo"<form action='' method='post'>\n","<div>";if(print_hidden_fields($_POST,["auth"]))echo"<p class='message'>".lang(139)."\n";echo"</div>\n";Admin::get()->printLoginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!DRIVER)print_login_page();if(isset($_GET["username"])&&!defined('AdminNeo\DRIVER_EXTENSION')){Admin::get()->addError(lang(140,implode(", ",Drivers::getExtensions(DRIVER))));unset($_SESSION["pwds"][DRIVER]);unset_permanent($qi);page_header(lang(141),false);page_footer("auth");exit;}if(!isset($_GET["username"])||get_password()===null)print_login_page();validate_server_input($qi);check_invalid_login($qi);Admin::get()->getConfig()->applyServer(SERVER);$e=connect_to_db($qi);authenticate($qi);create_driver($e);if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){Admin::get()->addError(lang(142));page_header(lang(6));page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($La&&$_POST["token"])$_POST["token"]=get_token();if($_POST){if(!verify_token()){$df="max_input_vars";$xg=ini_get($df);if(extension_loaded("suhosin")){foreach(["suhosin.request.max_vars","suhosin.post.max_vars"]as$u){$X=ini_get($u);if($X&&(!$xg||$X<$xg)){$df=$u;$xg=$X;}}}if(!$_POST["token"]&&$xg)Admin::get()->addError(lang(143,"'$df'"));else
Admin::get()->addError(lang(142).' '.lang(144));$_POST=[];}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$j=lang(145,"'post_max_size'");if(isset($_GET["sql"]))$j
.=' '.lang(146);Admin::get()->addError($j);}if(isset($_GET["settings"])){$P=Admin::get()->getSettings();$dk=array_merge(Admin::get()->getSettingsRows(1),Admin::get()->getSettingsRows(2),Admin::get()->getSettingsRows(3));if($_POST){$Zh=[];foreach($dk
as$u=>$K){if(isset($_POST[$u])){$Vl=$_POST[$u]===""||(is_array($_POST[$u])&&in_array("",$_POST[$u]));$Zh[$u]=(!$Vl?$_POST[$u]:null);}}$P->updateParameters($Zh);redirect(remove_from_uri());}$T=lang(131);page_header($T,[$T]);echo"<form id='settings' action='' method='post'>\n","<table class='box'>\n";foreach($dk
as$K)echo$K;echo"</table>\n","<p>","<input type='submit' value='".lang(112),"' class='button default hidden'>",input_token(),"</p>\n","</form>\n",script("initSettingsForm();");page_footer();exit;}if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];if(!(DB!=""?Connection::get()->selectDatabase(DB):isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill")){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!=""){Admin::get()->addError(lang(147));header("HTTP/1.1 404 Not Found");page_header(lang(30).": ".h(DB),true);}else{if($_POST["db"])queries_redirect(substr(ME,0,-1),lang(148),drop_databases($_POST["db"]));$T=h(Drivers::get(DRIVER).": ".Admin::get()->getServerName(SERVER));page_header($T,false);$dg=['privileges'=>[lang(72),"users"],'processlist'=>[lang(149),"list"],'variables'=>[lang(150),"variable"],'status'=>[lang(151),"status"],];$eg="";foreach($dg
as$u=>$X){if(support($u))$eg
.="<a href='".h(ME)."$u='>".icon($X[1])."$X[0]</a>";}if($eg)echo"<p class='links top-links'>$eg</p>\n";echo"<p>".lang(152,Drivers::get(DRIVER),"<b>".h(Connection::get()->getVersion())."</b>","<b>".DRIVER_EXTENSION."</b>")."\n","<p>".lang(153,"<b>".h(logged_user())."</b>")."\n";$g=Admin::get()->getDatabases();if($g){$Fj=support("scheme");$Ca=collations();echo"<form action='' method='post'>\n","<div class='table-footer-parent'>\n","<div class='scrollable'>\n","<table class='checkable'>\n","<thead><tr>".(support("database")?"<td>":"")."<th>".lang(30).(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".lang(154)."</a>":"")."<td>".lang(45)."<td>".lang(155)."<td>".lang(156)." - <a href='".h(ME)."dbsize=1'>".lang(157)."</a>".script("qsl('a').onclick = partial(ajaxSetHtml, '".js_escape(ME)."script=connect');","")."</thead>\n","<tbody>\n";$g=($_GET["dbsize"]?count_tables($g):array_flip($g));foreach($g
as$h=>$S){$rj=h(ME)."db=".urlencode($h);$r=h("Db-".$h);echo"<tr>".(support("database")?"<td class='actions'>".checkbox("db[]",$h,in_array($h,(array)$_POST["db"]),"","","",$r):""),"<th><a href='$rj' id='$r'>".h($h)."</a>";$_b=h(db_collation($h,$Ca));echo"<td>".(support("database")?"<a href='$rj".($Fj?"&amp;ns=":"")."&amp;database=' title='".lang(69)."'>$_b</a>":$_b),"<td align='right'><a href='$rj&amp;schema=' id='tables-".h($h)."' title='".lang(71)."'>".($_GET["dbsize"]?$S:"?")."</a>","<td align='right' id='size-".h($h)."'>".($_GET["dbsize"]?db_size($h):"?"),"\n";}echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),"</table>\n","</div>\n";if(support("database"))echo"<div class='table-footer'><div class='field-sets'>\n","<fieldset><legend>",lang(158)," <span id='selected'></span></legend><div class='fieldset-content'>\n",input_hidden("all"),script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^db/)); };"),"<input type='submit' class='button' name='drop' value='",lang(159),"'>",confirm(),"\n","</div></fieldset>\n","</div></div>\n",script("initTableFooter()");echo"</div>\n",input_token(),"</form>\n",script("tableCheck();");}}echo'<p class="links"><a href="'.h(ME).'database=">'.icon("database-add").lang(75)."</a>\n";page_footer("db");exit;}if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$l=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$M=[idf_escape($_GET["field"])];$I=Driver::get()->select($a,$M,[where($_GET,$l)],$M);$K=($I?$I->fetchRow():[]);echo
Connection::get()->formatValue($K[0],$l[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$l=fields($a);if(!$l)Admin::get()->addError(error()?:lang(78));$R=table_status1($a,true);$A=Admin::get()->getTableName($R);$qj=[];foreach($l
as$u=>$k)$qj+=$k["privileges"];$T=$l&&is_view($R)?$R['Engine']=='materialized view'?lang(160):lang(161):lang(8);$Rk=$A!=""?$A:h($a);page_header("$T: $Rk",[$Rk]);$O=null;if(isset($qj["insert"])||!support("table"))$O="";Admin::get()->printTableMenu($R,$O);$bf=[];if(!preg_match("~sqlite|mssql|pgsql~",DIALECT)&&isset($R["Engine"]))$bf[]=lang(162).": ".h($R["Engine"]);if(isset($R["Collation"]))$bf[]=lang(45).": ".h($R["Collation"]);if($bf)echo"<p>",implode(", ",$bf),"</p>";if($l)Admin::get()->printTableStructure($l);$Ib=$R["Comment"];if($Ib!="")echo"<p class='keep-lines'>",lang(46),": ",Admin::get()->formatComment($Ib),"</p>\n";if(!is_view($R))$Xc='<p class="links"><a href="'.h(ME).'create='.urlencode($a).'">'.icon("edit").lang(35)."</a>\n";elseif(support("view"))$Xc='<p class="links"><a href="'.h(ME).'view='.urlencode($a).'">'.icon("edit").lang(36)."</a>\n";else$Xc="";if($bf||$l||$Ib!="")echo$Xc;$ai=Driver::get()->getParentTables($a);if($ai){echo"<h2>".lang(163)."</h2>\n";Admin::get()->printRelatedTables($ai);}if(Driver::get()->getPartitionBy()&&str_contains(isset($R["Create_options"])?$R["Create_options"]:"","partitioned")){$ji=Driver::get()->getPartitionsInfo($a);if($ji){echo"<h2 id='partitions'>".lang(49)."</h2>\n";Admin::get()->printTablePartitions($ji);if(DIALECT!="pgsql")echo$Xc;}}$cf=Driver::get()->getInheritedTables($a);if($cf){echo"<h2 id='inherited-by'>".lang(164)."</h2>\n";Admin::get()->printRelatedTables($cf);}if(support("indexes")&&Driver::get()->supportsIndex($R)){echo"<h2 id='indexes'>".lang(165)."</h2>\n";$t=indexes($a);if($t)Admin::get()->printTableIndexes($t,$R);echo'<p class="links"><a href="'.h(ME).'indexes='.urlencode($a).'">'.icon("edit").lang(166)."</a>\n";}if(!is_view($R)){if(fk_support($R)){echo"<h2 id='foreign-keys'>".lang(89)."</h2>\n";$ae=foreign_keys($a);if($ae){echo"<table>\n","<thead><tr><th>".lang(167)."<td>".lang(168)."<td>".lang(92)."<td>".lang(91)."<td></thead>\n";foreach($ae
as$A=>$o)echo"<tr title='".h($A)."'>","<th><i>".implode("</i>, <i>",array_map('AdminNeo\h',$o["source"]))."</i>","<td><a href='".h($o["db"]!=""?preg_replace('~db=[^&]*~',"db=".urlencode($o["db"]),ME):($o["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".urlencode($o["ns"]),ME):ME))."table=".urlencode($o["table"])."'>".($o["db"]!=""&&$o["db"]!=DB?"<b>".h($o["db"])."</b>.":"").($o["ns"]!=""&&$o["ns"]!=$_GET["ns"]?"<b>".h($o["ns"])."</b>.":"").h($o["table"])."</a>","(<i>".implode("</i>, <i>",array_map('AdminNeo\h',$o["target"]))."</i>)","<td>".h($o["on_delete"]),"<td>".h($o["on_update"]),'<td><a href="'.h(ME.'foreign='.urlencode($a).'&name='.urlencode($A)).'">'.lang(169).'</a>',"\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'foreign='.urlencode($a).'">'.icon("add").lang(170)."</a>\n";}if(support("check")){echo"<h2 id='checks'>".lang(171)."</h2>\n";$pb=Driver::get()->checkConstraints($a);if($pb){echo"<table cellspacing='0'>\n";foreach($pb
as$u=>$X)echo"<tr title='".h($u)."'>","<td><code class='jush-".DIALECT."'>".h($X),"<td><a href='".h(ME.'check='.urlencode($a).'&name='.urlencode($u))."'>".lang(169)."</a>","\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'check='.urlencode($a).'">'.icon("add").lang(172)."</a>\n";}}if(support(is_view($R)?"view_trigger":"trigger")){echo"<h2 id='triggers'>".lang(173)."</h2>\n";$Cl=triggers($a);if($Cl){echo"<table>\n";foreach($Cl
as$u=>$X)echo"<tr><td>".h($X[0])."<td>".h($X[1])."<th>".h($u)."<td><a href='".h(ME.'trigger='.urlencode($a).'&name='.urlencode($u))."'>".lang(169)."</a>\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'trigger='.urlencode($a).'">'.icon("add").lang(174)."</a>\n";}}elseif(isset($_GET["schema"])){$ql=h(": ".DB.($_GET["ns"]?".$_GET[ns]":""));page_header(lang(71).$ql,[lang(71)]);$Tk=[];$Uk=[];$Jd=[];$oa=($_GET["schema"]?:$_COOKIE["neo_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$oa,$z,PREG_SET_ORDER);foreach($z
as$q=>$y){$Tk[$y[1]]=[(float)$y[2],(float)$y[3]];$Uk[]="\n\t'".js_escape($y[1])."': [ $y[2], $y[3] ]";}$vl=0;$Ua=-1;$Dj=[];$dj=[];$Tf=[];$Da=Driver::get()->getAllFields();foreach(table_status('',true)as$Q=>$R){if(is_view($R))continue;$G=0;$Dj[$Q]["fields"]=[];foreach(isset($Da[$Q])?$Da[$Q]:[]as$k){$G+=1.25;$Jd[$Q][$k["field"]]=$G;$Dj[$Q]["fields"][$k["field"]]=$k;}$Dj[$Q]["pos"]=(isset($Tk[$Q])?$Tk[$Q]:[$vl,0]);foreach(Admin::get()->getForeignKeys($Q)as$X){if(!$X["db"]){$Rf=$Ua;if((isset($Tk[$Q][1])?$Tk[$Q][1]:0)||(isset($Tk[$X["table"]][1])?$Tk[$X["table"]][1]:0))$Rf=min(floatval(isset($Tk[$Q][1])?$Tk[$Q][1]:0),floatval(isset($Tk[$X["table"]][1])?$Tk[$X["table"]][1]:0))-1;else$Ua-=.1;while($Tf[(string)$Rf])$Rf-=.0001;$Dj[$Q]["references"][$X["table"]][(string)$Rf]=[$X["source"],$X["target"]];$dj[$X["table"]][$Q][(string)$Rf]=$X["target"];$Tf[(string)$Rf]=true;}}$vl=max($vl,$Dj[$Q]["pos"][0]+2.5+$G);}echo"<div id='schema' style='height: {$vl}em;'>\n","<script",nonce(),">\n","gid('schema').onselectstart = () => false;\n","const tablePos = {",implode(",",$Uk),"\n};\n","const em = gid('schema').offsetHeight / $vl;\n","document.onmousemove = schemaMousemove;\n","document.onmouseup = partialArg(schemaMouseup, '",js_escape(DB),"');\n","</script>\n";foreach($Dj
as$A=>$Q){echo"<div class='table' style='top: ".$Q["pos"][0]."em; left: ".$Q["pos"][1]."em;'>",'<a href="'.h(ME).'table='.urlencode($A).'"><b>'.h($A)."</b></a>",script("qsl('div').onmousedown = schemaMousedown;");foreach($Q["fields"]as$k){$X='<span '.type_class($k["type"]).' title="'.h($k["type"].($k["length"]?"($k[length])":"").($k["null"]?" NULL":'')).'">'.h($k["field"]).'</span>';echo"<br>".($k["primary"]?"<i>$X</i>":$X);}foreach((array)$Q["references"]as$el=>$fj){foreach($fj
as$Rf=>$Zi){$Sf=$Rf-(isset($Tk[$A][1])?$Tk[$A][1]:0);$q=0;foreach($Zi[0]as$lk){echo"\n<div class='references' title='",h($el),"' id='refs$Rf-$q' style='left: {$Sf}em; top: ",$Jd[$A][$lk],"em; padding-top: .5em;'>","<div style='border-top: 1px solid Gray; width: ".(-$Sf)."em;'></div>","</div>";$q++;}}}foreach((array)$dj[$A]as$el=>$fj){foreach($fj
as$Rf=>$c){$Sf=$Rf-(isset($Tk[$A][1])?$Tk[$A][1]:0);$q=0;foreach($c
as$dl){echo"\n<div class='references' title='",h($el),"' id='refd$Rf-$q' style='left: {$Sf}em; top: ".$Jd[$A][$dl]."em; height: 1.25em;'>","<svg style='width: 1em; height: 1em; float: right;' viewBox='0 0 22 22' fill='currentColor'><path d='M11,19l10,-8l-10,-8l0,16Z'/></svg>","<div style='height: .5em; border-bottom: 1px solid Gray; width: ".(-$Sf)."em;'></div>","</div>";$q++;}}}echo"\n</div>\n";}foreach($Dj
as$A=>$Q){foreach((array)$Q["references"]as$el=>$fj){if($Dj[$el]){foreach($fj
as$Rf=>$Zi){$Jg=$vl;$ug=-10;foreach($Zi[0]as$u=>$lk){$zi=$Q["pos"][0]+$Jd[$A][$lk];$_i=$Dj[$el]["pos"][0]+$Jd[$el][$Zi[1][$u]];$Jg=min($Jg,$zi,$_i);$ug=max($ug,$zi,$_i);}echo"<div class='references' id='refl$Rf' style='left: $Rf"."em; top: $Jg"."em; padding: .5em 0;'><div style='border-right: 1px solid Gray; margin-top: 1px; height: ".($ug-$Jg)."em;'></div></div>\n";}}}}echo"</div>\n","<p class='links'>","<a href='",(ME."schema=".urlencode($oa)),"' id='schema-link'>",lang(175),"</a>","</p>\n";}elseif(isset($_GET["dump"])){$a=$_GET["dump"];$P=Admin::get()->getSettings();if($_POST){$P->updateParameters(["dumpFormat"=>$_POST["format"],"dumpDbStyle"=>$_POST["db_style"],"dumpTypes"=>isset($_POST["types"])?$_POST["types"]:(support("type")?"":null),"dumpRoutines"=>isset($_POST["routines"])?$_POST["routines"]:(support("routine")?"":null),"dumpEvents"=>isset($_POST["events"])?$_POST["events"]:(support("event")?"":null),"dumpTableStyle"=>$_POST["table_style"],"dumpAutoIncrement"=>isset($_POST["auto_increment"])?$_POST["auto_increment"]:"","dumpTriggers"=>isset($_POST["triggers"])?$_POST["triggers"]:(support("trigger")?"":null),"dumpDataStyle"=>$_POST["data_style"],"dumpOutput"=>$_POST["output"],]);if(DB!="")$g=[DB];else{$g=isset($_POST["databases"])?$_POST["databases"]:[];if(is_string($g))$g=explode("\n",rtrim(str_replace("\r","",$g),"\n"));}$Ej=isset($_POST["schemas"])?$_POST["schemas"]:[];$S=array_flip(isset($_POST["tables"])?$_POST["tables"]:[])+array_flip(isset($_POST["data"])?$_POST["data"]:[]);if(count($S)==1)$Qe=key($S);elseif(count($Ej)==1)$Qe=$Ej[0];elseif(count($g)==1)$Qe=$g[0];else$Qe=Admin::get()->getServerName(SERVER,true,"server");$yd=dump_headers($Qe,DB==""||$_GET["ns"]===""||count($S)>1);$sf=preg_match('~sql~',$_POST["format"]);$jc=$sf&&$_POST["data_style"]&&!$_POST["table_style"]&&DIALECT!="sql";if($sf){echo"-- AdminNeo ".VERSION." ".Drivers::get(DRIVER)." ".Connection::get()->getVersion()." dump\n\n";if(DIALECT=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";Connection::get()->query("SET time_zone = '+00:00'");Connection::get()->query("SET sql_mode = ''");}}$_k=$_POST["db_style"];foreach($g
as$h){Admin::get()->dumpDatabase($h);if(Connection::get()->selectDatabase($h)){if($sf){if($_k)echo
create_database_sql($h,$_k),use_sql($h,$_k)."\n";$Th="";if($_POST["types"]){foreach(types()as$r=>$U){$kd=type_values($r);if($kd)$Th
.=($_k!='DROP+CREATE'?"DROP TYPE IF EXISTS ".idf_escape($U).";;\n":"")."CREATE TYPE ".idf_escape($U)." AS ENUM ($kd);\n\n";else$Th
.="-- Could not export type $U\n\n";}}if($_POST["routines"]){foreach(routines()as$K){$A=$K["ROUTINE_NAME"];$sj=$K["ROUTINE_TYPE"];$Zb=create_routine($sj,["name"=>$A]+routine($K["SPECIFIC_NAME"],$sj));set_utf8mb4($Zb);$Th
.=($_k!='DROP+CREATE'?"DROP $sj IF EXISTS ".idf_escape($A).";;\n":"")."$Zb;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$K){$Zb=remove_definer(Connection::get()->getValue("SHOW CREATE EVENT ".idf_escape($K["Name"]),3));set_utf8mb4($Zb);$Th
.=($_k!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($K["Name"]).";;\n":"")."$Zb;;\n\n";}}echo($Th&&DIALECT=='sql'?"DELIMITER ;;\n\n$Th"."DELIMITER ;\n\n":$Th);}if($_POST["table_style"]||$_POST["data_style"]){foreach(($_GET["ns"]===""?(array)$_POST["schemas"]:(DB!=""||!support("scheme")?[""]:Admin::get()->getSchemas(true)))as$Dj){if($Dj!="")set_schema($Dj);$Zk=table_status('',true);$Sk=array_keys($Zk);$Dc=false;if($jc&&$Sk){$ej=[];foreach($Sk
as$A){if(!is_view($Zk[$A])&&(DB==""||$_GET["ns"]===""||in_array($A,(array)$_POST["data"]))){foreach(foreign_keys($A)as$o)$ej[$A][]=$o["table"];}}$Jh=dump_table_order($Sk,$ej);if($Jh)$Sk=$Jh;else$Dc=function_exists('AdminNeo\foreign_key_checks_sql');}if($Dc)echo
foreign_key_checks_sql(false)."\n";$mm=[];foreach($Sk
as$A){$R=$Zk[$A];$Q=(DB==""||$_GET["ns"]===""||in_array($A,(array)$_POST["tables"]));$f=(DB==""||$_GET["ns"]===""||in_array($A,(array)$_POST["data"]));if($Q||$f){$sl=null;if($yd=="tar"){$sl=new
TmpFile();ob_start([$sl,'write'],1e5);}$ac=($Q?$_POST["table_style"]:"");Admin::get()->dumpTable($A,$ac,(is_view($R)?2:0));if(is_view($R)&&$yd!="tar")$mm[]=$A;elseif($f){$l=fields($A);Admin::get()->dumpData($A,$_POST["data_style"],"SELECT *".convert_fields($l,$l)." FROM ".table($A));if($sf&&!$ac&&$_POST["auto_increment"]&&function_exists('AdminNeo\restart_sequences_sql'))echo"\n".restart_sequences_sql($A);}if($sf&&$_POST["triggers"]&&$Q&&($Cl=trigger_sql($A)))echo"\nDELIMITER ;;\n$Cl\nDELIMITER ;\n";if($yd=="tar"){ob_end_flush();tar_file((DB!=""?"":"$h/")."$A.csv",$sl);}elseif($sf)echo"\n";}}if($Dc)echo
foreign_key_checks_sql(true)."\n";if($_POST["table_style"]&&function_exists('AdminNeo\foreign_keys_sql')){foreach($Zk
as$A=>$R){$Q=(DB==""||$_GET["ns"]===""||in_array($A,(array)$_POST["tables"]));if($Q&&!is_view($R))echo
foreign_keys_sql($A);}}foreach($mm
as$km)Admin::get()->dumpTable($km,$_POST["table_style"],1);if($yd=="tar")echo
pack("x512");}}}}if($sf)echo"-- ".gmdate("Y-m-d H:i:s e")."\n";exit;}$A=DB!=""?h(DB):h(Admin::get()->getServerName(SERVER));page_header(lang(74).": $A",($_GET["export"]!=""?["table"=>$_GET["export"]]:[lang(74)]));echo"<form action='' method='post'>\n","<table class='box'>\n";$nc=['','USE','DROP+CREATE','CREATE'];$Wk=['','DROP+CREATE','CREATE'];$kc=['','TRUNCATE+INSERT','INSERT'];if(DIALECT=="sql")$kc[]='INSERT+UPDATE';echo"<tr><th>",lang(176),"</th><td>",html_radios("format",Admin::get()->getDumpFormats(),$P->getParameter("dumpFormat","sql")),"</td></tr>\n";if(DIALECT!="sqlite"){echo"<tr><th id='label-db'>",lang(30),"</th>","<td>",html_select('db_style',$nc,$P->getParameter("dumpDbStyle",DB==""?"CREATE":""),"","label-db"),"<span class='labels'>";if(support("routine"))echo
checkbox("routines",1,$P->getParameter("dumpRoutines",$_GET["dump"]==""?"1":""),lang(177));if(support("event"))echo
checkbox("events",1,$P->getParameter("dumpEvents",$_GET["dump"]==""?"1":""),lang(178));echo"</span></td></tr>";}echo"<tr><th id='label-tables'>",lang(155),"</th><td>",html_select('table_style',$Wk,$P->getParameter("dumpTableStyle","DROP+CREATE"),"","label-tables")," <span class='labels'>",checkbox("auto_increment",1,$P->getParameter("dumpAutoIncrement"),lang(47));if(support("trigger"))echo
checkbox("triggers",1,$P->getParameter("dumpTriggers","1"),lang(173));echo"</span></td></tr>","<tr><th id='label-data'>",lang(179),"</th><td>",html_select("data_style",$kc,$P->getParameter("dumpDataStyle","INSERT"),"","label-data"),"</td></tr>","<tr><th>",lang(180),"</th><td>",html_radios("output",Admin::get()->getDumpOutputs(),$P->getParameter("dumpOutput","file")),"</td></tr>\n","</table>\n","<p>","<input type='submit' class='button default' value='",lang(74),"'>",input_token(),"</p>\n","<table>\n",script("qsl('table').onclick = dumpClick;");$Di=[];if(DB!=""&&$_GET["ns"]===""){echo"<thead><tr><th>","<label class='block'><input type='checkbox' id='check-schemas' checked class='jsonly'>".lang(181)."</label>".script("gid('check-schemas').onclick = partial(formCheck, /^schemas\\[/);",""),"</thead>\n";foreach(Admin::get()->getSchemas()as$Dj)echo"<tr><td>".checkbox("schemas[]",$Dj,true,$Dj,"","block")."\n";}elseif(DB!=""){$rb=($a!=""?"":" checked");echo"<thead><tr>","<th><label class='block'><input type='checkbox' id='check-tables'$rb class='jsonly'>".lang(8)."</label>".script("gid('check-tables').onclick = partial(formCheck, /^tables\\[/);",""),"<th class='right'><label class='block'>".lang(179)."<input type='checkbox' id='check-data'$rb class='jsonly'></label>".script("gid('check-data').onclick = partial(formCheck, /^data\\[/);",""),"</thead>\n";$mm="";$Yk=tables_list();foreach($Yk
as$A=>$U){$Ci=preg_replace('~_.*~','',$A);$rb=($a==""||$a==(substr($a,-1)=="%"?"$Ci%":$A));$Gi="<tr><td>".checkbox("tables[]",$A,$rb,$A,"","block");if($U!==null&&!preg_match('~table~i',$U))$mm
.="$Gi\n";else
echo"$Gi<td class='right'><label class='block'><span id='Rows-".h($A)."'></span>".checkbox("data[]",$A,$rb)."</label>\n";$Di[$Ci]++;}echo$mm;if($Yk)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{$g=Admin::get()->getDatabases();echo"<thead><tr><th>","<label class='block'>".($g?"<input type='checkbox' id='check-databases'".($a==""?" checked":"")." class='jsonly'>".script("gid('check-databases').onclick = partial(formCheck, /^databases\\[/);",""):"").lang(30)."</label>","</thead>\n";if($g){foreach($g
as$h){if(!information_schema($h)){$Ci=preg_replace('~_.*~','',$h);echo"<tr><td>".checkbox("databases[]",$h,$a==""||$a=="$Ci%",$h,"","block")."\n";$Di[$Ci]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo"</table>\n","</form>\n";$dg=[];foreach($Di
as$u=>$X){if($u!=""&&$X>1)$dg[]="<a href='".h(ME)."dump=".urlencode("$u%")."'>".icon("check").h($u)."*</a>";}if($dg)echo"<p class='links'>",implode("",$dg),"</p>\n";}elseif(isset($_GET["privileges"])){$ql=DB!=""?h(": ".DB):"";page_header(lang(72).$ql,[lang(72)]);echo'<p class="links top-links"><a href="',h(ME),'user=">',icon("user-add"),lang(182),"</a></p>\n";$I=Connection::get()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$qe=$I;if(!$I)$I=Connection::get()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''>\n";hidden_fields_get();echo
input_hidden("db",DB);if(!$qe)echo
input_hidden("grant");echo"\n","<div class='scrollable'>\n","<table class='checkable'>\n","<thead><tr><th>".lang(28)."<th>".lang(5)."<th></thead>\n";while($K=$I->fetchAssoc())echo'<tr><td>'.h($K["User"])."<td>".h($K["Host"]).'<td><a href="'.h(ME.'user='.urlencode($K["User"]).'&host='.urlencode($K["Host"])).'">'.lang(38)."</a>\n";if(!$qe||DB!="")echo"<tr><td><input class='input' name='user' autocapitalize='off'><td><input class='input' name='host' value='localhost' autocapitalize='off'><td><input type='submit' class='button' value='".lang(38)."'>\n";echo"</table>\n","</div>\n","</form>\n";}elseif(isset($_GET["sql"])){$P=Admin::get()->getSettings();if($_POST["export"]){$P->updateParameters(["exportFormat"=>$_POST["format"],"exportOutput"=>$_POST["output"],]);dump_headers("sql");Admin::get()->dumpTable("","");Admin::get()->dumpData("","table",$_POST["query"]);exit;}restart_session();$Ie=&get_session("queries");$He=&$Ie[DB];if($_POST["clear"]){$He=[];redirect(remove_from_uri("history"));}stop_session();$T=isset($_GET["import"])?lang(73):lang(40);page_header($T,[$T]);$ag="--".(DIALECT=="sql"?" ":"");if($_POST){$he=false;if(!isset($_GET["import"]))$H=$_POST["query"];elseif($_POST["webfile"]){$Ue=Admin::get()->getImportFilePath();if($Ue){if(file_exists($Ue))$he=fopen($Ue,"rb");elseif(file_exists("$Ue.gz"))$he=fopen("compress.zlib://$Ue.gz","rb");}$H=$he?fread($he,1e6):false;}else$H=get_file("sql_file",true,";");if(is_string($H)){if(($_g=ini_bytes("memory_limit"))!="-1")ini_set("memory_limit",max($_g,strval(2*strlen($H)+memory_get_usage()+8e6)));if($H!=""&&strlen($H)<1e6){$Oi=$H.(preg_match("~;[ \t\r\n]*\$~",$H)?"":";");if(!$He||first(end($He))!=$Oi){restart_session();$He[]=[$Oi,time()];set_session("queries",$Ie);stop_session();}}$nk="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|$ag)[^\n]*\n?|--\r?\n)";$wc=";";$xc=1;$nh=0;$dd=true;$Rb=connect();if($Rb&&DB!=""){$Rb->selectDatabase(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$Rb);}$Hb=0;$md=[];$bi='[\'"'.(DIALECT=="sql"?'`#':(DIALECT=="sqlite"?'`[':(DIALECT=="mssql"?'[':''))).']|/\*|'.$ag.'|$'.(DIALECT=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$wl=microtime(true);$Uc=Admin::get()->getDumpFormats();unset($Uc["sql"]);while($H!=""){if(!$nh&&preg_match("~^$nk*+DELIMITER\\s+(\\S+)~i",$H,$y)){$wc=preg_quote($y[1]);$xc=strlen($y[1]);$de=Admin::get()->formatSqlCommandQuery(trim($y[0]));if($de!="")echo"<pre><code class='jush-".DIALECT."'>$de</code></pre>\n";$H=substr($H,strlen($y[0]));}elseif(!$nh&&DIALECT=="pgsql"&&preg_match("~^($nk*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$H,$y)){$wc="\n\\\\\\.\r?\n";$xc=3;$nh=strlen($y[0]);}else{preg_match("($wc\\s*|$bi)",$H,$y,PREG_OFFSET_CAPTURE,$nh);list($fe,$G)=$y[0];if(!$fe&&$he&&!feof($he))$H
.=fread($he,1e5);else{if(!$fe&&rtrim($H)=="")break;$nh=$G+strlen($fe);if($fe&&!preg_match("(^$wc)",$fe)){$gb=Driver::get()->hasCStyleEscapes()||(DIALECT=="pgsql"&&($G>0&&strtolower($H[$G-1])=="e"));$oi='(';if($fe=='/*')$oi
.='\*/';elseif($fe=='[')$oi
.=']';elseif(preg_match("~^$ag|^#~",$fe))$oi
.="\n";else$oi
.=preg_quote($fe).($gb?"|\\\\.":"");$oi
.='|$)s';while(preg_match($oi,$H,$y,PREG_OFFSET_CAPTURE,$nh)){$wj=$y[0][0];if(!$wj&&$he&&!feof($he))$H
.=fread($he,1e5);else{$nh=$y[0][1]+strlen($wj);if(!isset($wj[0])||$wj[0]!="\\")break;}}}else{$dd=false;$Oi=substr($H,0,$G+$xc);$Hb++;$Gi="<pre id='sql-$Hb'><code class='jush-".DIALECT."'>".Admin::get()->formatSqlCommandQuery(trim($Oi))."</code></pre>\n";if(DIALECT=="sqlite"&&preg_match("~^$nk*+(ATTACH|VACUUM\\b.*\\bINTO)\\b~is",$Oi,$y)!==0){echo$Gi,"<p class='error'>".lang(183,preg_match('~ATTACH~i',$y[1])?'ATTACH':'VACUUM INTO')."\n";$md[]=" <a href='#sql-$Hb'>$Hb</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$Gi;ob_flush();flush();}$sk=microtime(true);if(Connection::get()->multiQuery($Oi)&&is_object($Rb)&&preg_match("~^$nk*+USE\\b~i",$Oi))$Rb->query($Oi);do{$I=Connection::get()->storeResult();if(Connection::get()->getError()){echo($_POST["only_errors"]?$Gi:""),"<p class='error'>",lang(184),(!empty(Connection::get()->getErrno())?" (".Connection::get()->getErrno().")":""),": ",error()."</p>\n";$md[]=" <a href='#sql-$Hb'>$Hb</a>";if($_POST["error_stops"])break
2;}else{$nl=" <span class='time'>(".format_time($sk).")</span>";$Yc=(strlen($Oi)<1000?" <a href='".h(ME)."sql=".urlencode(trim($Oi))."'>".icon("edit").lang(38)."</a>":"");$Si=Connection::get()->getQueryInfo();$ya=Connection::get()->getAffectedRows();$sm=($_POST["only_errors"]?null:Driver::get()->warnings());$um="warnings-$Hb";$vm=$sm?"<a href='#$um' class='toggle'>".lang(39).icon_chevron_down()."</a>":null;$ud=$Mh=null;$vd="explain-$Hb";$wd=false;$xd="export-$Hb";if(is_object($I)){if(!$_POST["only_errors"])echo"<div class='table-result'>\n";$w=(int)$_POST["limit"];$Mh=print_select_result($I,$Rb,[],$w);if(!$_POST["only_errors"]){echo"<p class='links'>";$kh=$I->getRowsCount();echo($kh?($w&&$kh>$w?lang(185,$w):"").lang(186,$kh):""),$nl,$Yc,$vm;if($Rb&&preg_match("~^($nk|\\()*+SELECT\\b~i",$Oi)&&($ud=explain($Rb,$Oi)))echo"<a href='#$vd' class='toggle'>Explain".icon_chevron_down()."</a>";$wd=true;echo"<a href='#$xd' class='toggle'>".lang(74).icon_chevron_down()."</a>","</p>\n";}}else{if(preg_match("~^$nk*+(CREATE|DROP|ALTER)$nk++(DATABASE|SCHEMA)\\b~i",$Oi)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"]){echo"<p class='message' title='".h($Si)."'>",lang(187,$ya),"$nl $Yc";if($vm)echo", $vm";echo"</p>\n";}}if(!$_POST["only_errors"])echo
script("initToggles(qsl('p'));");if($sm)echo"<div id='$um' class='hidden'>\n$sm</div>\n";if($ud){echo"<div id='$vd' class='hidden explain'>\n";print_select_result($ud,$Rb,$Mh);echo"</div>\n";}if($wd)echo"<form id='$xd' action='' method='post' class='hidden'><p>\n",html_select("format",$Uc,$P->getParameter("exportFormat")),html_select("output",Admin::get()->getDumpOutputs(),$P->getParameter("exportOutput"))." ",input_hidden("query",$Oi),input_token()," <input type='submit' class='button' name='export' value='".lang(74)."'>","</p></form>\n";if(is_object($I)&&!$_POST["only_errors"])echo"</div>\n";}$sk=microtime(true);}while(Connection::get()->nextResult());}$H=substr($H,$nh);$nh=0;}}}}if($dd)echo"<p class='message'>".lang(188)."\n";elseif($_POST["only_errors"]){$qh=$Hb-count($md);echo"<p class='".($qh?"message":"error")."'>".lang(189,$Hb-count($md))," <span class='time'>(".format_time($wl).")</span>\n";}elseif($md&&$Hb>1)echo"<p class='error'>".lang(184).": ".implode("",$md)."\n";}else
echo"<p class='error'>".upload_error($H)."\n";}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";if(!isset($_GET["import"])){$Oi=$_GET["sql"];if($_POST)$Oi=$_POST["query"];elseif($_GET["history"]=="all")$Oi=$He;elseif($_GET["history"]!="")$Oi=$He[$_GET["history"]][0];echo"<p>";textarea("query",$Oi,20);echo
script(($_POST?"":"qs('textarea').focus();\n")."gid('form').onsubmit = partial(sqlSubmit, gid('form'), '".js_escape(remove_from_uri("sql|limit|error_stops|only_errors|history"))."');"),"</p>","<p><input type='submit' class='button default' value='".lang(190)."' title='Ctrl+Enter'>",lang(191).": <input type='number' name='limit' class='input size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{echo"<div class='field-sets'>\n","<fieldset><legend>".lang(192)."</legend><div class='fieldset-content'>";$ye=(extension_loaded("zlib")?"[.gz]":"");if(ini_bool("file_uploads"))echo"SQL$ye (&lt; ".ini_get("upload_max_filesize")."B): <input type='file' name='sql_file[]' multiple>","<input type='submit' class='button default' value='".lang(190)."'>",file_upload_form_script("form","sql_file[]");else
echo
lang(193);echo"</div></fieldset>\n";$Ue=Admin::get()->getImportFilePath();if($Ue)echo"<fieldset><legend>".lang(194)."</legend><div class='fieldset-content'>",lang(195,"<code>".h($Ue)."$ye</code>"),' <input type="submit" class="button default" name="webfile" value="'.lang(196).'">',"</div></fieldset>\n";echo"</div>\n","<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:isset($_GET["import"])||$_GET["error_stops"]),lang(197)),checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),lang(198)),input_token(),"</p>\n";if(!isset($_GET["import"]))Admin::get()->printAfterSqlCommand();if(!isset($_GET["import"])&&$He){echo"<div class='field-sets'>\n";print_fieldset_start("history",lang(199),"history",$_GET["history"]!="");for($X=end($He);$X;$X=prev($He)){$u=key($He);list($Oi,$nl,$cd)=$X;echo" <pre><code class='jush-".DIALECT."'>",truncate_utf8(ltrim(str_replace("\n"," ",str_replace("\r","",preg_replace("~^(#|$ag).*~m",'',$Oi))))),"</code></pre>",'<p class="links">',"<a href='".h(ME."sql=&history=$u")."'>".icon("edit").lang(38)."</a>"," <span class='time' title='".@date('Y-m-d',$nl)."'>".@date("H:i:s",$nl).($cd?" ($cd)":"")."</span>","</p>";}echo"<p><input type='submit' class='button' name='clear' value='".lang(200)."'>\n","<a href='",h(ME."sql=&history=all")."' class='button light'>",icon("edit"),lang(201),"</a></p>\n";print_fieldset_end("history");echo"</div>\n";}echo"</form>\n";}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$l=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$l):""):where($_GET,$l));$Rl=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($l
as$A=>$k){if((!$Rl&&!isset($k["privileges"]["insert"]))||Admin::get()->getFieldName($k)=="")unset($l[$A]);}if($_POST&&!isset($_GET["select"])){$lg=$_POST["referer"];if($_POST["insert"])$lg=($Rl?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$lg))$lg=ME."select=".urlencode($a);$t=indexes($a);$Ll=unique_array(isset($_GET["where"])?$_GET["where"]:[],$t);$Ti="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($lg,lang(202),(bool)Driver::get()->delete($a,$Ti,$Ll?0:1));else{$O=[];foreach($l
as$A=>$k){$X=process_input($k);if($X!==false&&$X!==null)$O[idf_escape($A)]=$X;}if($Rl){if(!$O)redirect($lg);queries_redirect($lg,lang(203),(bool)Driver::get()->update($a,$O,$Ti,$Ll?0:1));if(is_ajax()){page_headers();page_messages();exit;}}else{$I=Driver::get()->insert($a,$O);$Pf=($I?last_id($I):0);queries_redirect($lg,lang(204,($Pf?" $Pf":"")),(bool)$I);}}}$K=null;if($Z){$M=[];foreach($l
as$A=>$k){if(isset($k["privileges"]["select"])){$Ja=($_POST["clone"]&&$k["auto_increment"]?"''":convert_field($k));$M[]=($Ja?"$Ja AS ":"").idf_escape($A);}}$K=[];if(!support("table"))$M=["*"];if($M){$I=Driver::get()->select($a,$M,[$Z],$M,[],(isset($_GET["select"])?2:1));if(!$I)Admin::get()->addError(error());else{$K=$I->fetchAssoc();if(!$K)$K=false;}if(isset($_GET["select"])&&(!$K||$I->fetchAssoc()))$K=null;}}if(!support("table")&&!$l){if(!$Z){$I=Driver::get()->select($a,["*"],[],["*"]);$K=($I?$I->fetchAssoc():false);if(!$K)$K=[Driver::get()->primary=>""];}if($K){foreach($K
as$u=>$X){if(!$Z)$K[$u]=null;$l[$u]=["field"=>$u,"null"=>($u!=Driver::get()->primary),"auto_increment"=>($u==Driver::get()->primary)];}}}if(isset($_POST["save"])?$_POST["save"]:false)$K=(isset($_POST["fields"])?$_POST["fields"]:[])+($K?:[]);if($_POST["edit"]){$ad=array_filter($l,function($k){return!(isset($k["generated"])?$k["generated"]:null);});}else$ad=$l;edit_form($a,$ad,$K,$Rl);}elseif(isset($_GET["create"])){$a=$_GET["create"];$fi=Driver::get()->getPartitionBy();$ji=$fi?Driver::get()->getPartitionsInfo($a):[];$bj=referencable_primary($a);$ae=[];foreach($bj
as$Rk=>$k)$ae[str_replace("`","``",$Rk)."`".str_replace("`","``",$k["field"])]=$Rk;$Ph=[];$R=[];if($a!=""){$Ph=fields($a);$R=table_status1($a);if(count($R)<2)Admin::get()->addError(lang(78));}$K=$_POST;$K["fields"]=(array)$K["fields"];if($K["auto_increment_col"])$K["fields"][$K["auto_increment_col"]]["auto_increment"]=true;if($_POST&&!Admin::get()->getErrors())Admin::get()->getSettings()->updateParameter("commentsOpened",isset($_POST["comments"])?$_POST["comments"]:null);if($_POST&&!process_fields($K["fields"])&&!Admin::get()->getErrors()){if($_POST["drop"])queries_redirect(substr(ME,0,-1),lang(205),drop_tables([$a]));else{$l=[];$Da=[];$Wl=false;$Yd=[];$Oh=reset($Ph);$_a=" FIRST";foreach($K["fields"]as$u=>$k){$o=$ae[$k["type"]];$Fl=($o!==null?$bj[$o]:$k);if($k["field"]!=""){if(!$k["generated"])$k["default"]=null;$Mi=process_field($k,$Fl);$Da[]=[$k["orig"],$Mi,$_a];if(!$Oh||$Mi!==process_field($Oh,$Oh)){$l[]=[$k["orig"],$Mi,$_a];if($k["orig"]!=""||$_a)$Wl=true;}if($o!==null)$Yd[idf_escape($k["field"])]=($a!=""&&DIALECT!="sqlite"?"ADD":" ").format_foreign_key(['table'=>$ae[$k["type"]],'source'=>[$k["field"]],'target'=>[$Fl["field"]],'on_delete'=>$k["on_delete"],]);$_a=" AFTER ".idf_escape($k["field"]);}elseif($k["orig"]!=""){$Wl=true;$l[]=[$k["orig"]];}if($k["orig"]!=""){$Oh=next($Ph);if(!$Oh)$_a="";}}$hi=[];if(in_array($K["partition_by"],$fi)){foreach($K
as$u=>$X){if(preg_match('~^partition~',$u))$hi[$u]=$X;}foreach($hi["partition_names"]as$u=>$A){if($A===""){unset($hi["partition_names"][$u]);unset($hi["partition_values"][$u]);}}$hi["partition_names"]=array_values($hi["partition_names"]);$hi["partition_values"]=array_values($hi["partition_values"]);if($hi==$ji)$hi=[];}elseif(str_contains(isset($R["Create_options"])?$R["Create_options"]:"","partitioned"))$hi=null;$_=lang(206);if($a==""){cookie("neo_engine",isset($K["Engine"])?$K["Engine"]:"");$_=lang(207);}$A=trim($K["name"]);queries_redirect(ME.(support("table")?"table=":"select=").urlencode($A),$_,alter_table($a,$A,(DIALECT=="sqlite"&&($Wl||$Yd)?$Da:$l),$Yd,($K["Comment"]!=$R["Comment"]?$K["Comment"]:null),($K["Engine"]&&$K["Engine"]!=$R["Engine"]?$K["Engine"]:""),($K["Collation"]&&$K["Collation"]!=$R["Collation"]?$K["Collation"]:""),($K["Auto_increment"]!=""?number($K["Auto_increment"]):""),$hi));}}if($a!="")page_header(lang(35).": ".h($a),["table"=>$a,lang(35)]);else
page_header(lang(77),[lang(77)]);if(!$_POST){$Hl=Driver::get()->getTypes();$K=["Engine"=>$_COOKIE["neo_engine"],"fields"=>[["field"=>"","type"=>(isset($Hl["int"])?"int":(isset($Hl["integer"])?"integer":"")),"on_update"=>""]],"partition_names"=>[""],];if($a!=""){$K=$R;$K["name"]=$a;$K["fields"]=[];if(!$_GET["auto_increment"])$K["Auto_increment"]="";foreach($Ph
as$k){$k["generated"]=$k["generated"]?:(isset($k["default"])?"DEFAULT":"");$K["fields"][]=$k;}if($fi){$K+=$ji;$K["partition_names"][]="";$K["partition_values"][]="";}}}$Af=[];if($K["Collation"])$Af[$K["Collation"]]=true;foreach($K["fields"]as$k){if($k["collation"])$Af[$k["collation"]]=true;}$Ab=Admin::get()->getCollations(array_keys($Af));$id=Driver::get()->engines();foreach($id
as$hd){if(!strcasecmp($hd,$K["Engine"])){$K["Engine"]=$hd;break;}}echo"<form action='' method='post' id='form'>\n";if(support("columns")||$a==""){echo"<p>",lang(208),": ","<input class='input' name='name' data-maxlength='64' value='",h($K["name"]),"' autocapitalize='off'",(($a==""&&!$_POST)?" autofocus":""),">";if($id)echo" ",html_select("Engine",[""=>"(".lang(209).")"]+$id,$K["Engine"]),help_script_command("value",true);if($Ab&&!preg_match("~sqlite|mssql~",DIALECT))echo" ",html_select("Collation",[""=>"(".lang(90).")"]+$Ab,$K["Collation"]);echo" <input type='submit' class='button default' value='",lang(112),"'>","</p>";}if(support("columns")&&($a==""||!Driver::get()->isPartition($a))){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($K["fields"],$Ab,"TABLE",$ae);echo"</table>\n",script("initFieldsEditing(gid('edit-fields'));");if(support("move_col"))echo
script("initSortable('#edit-fields tbody');");echo"</div>\n","<p>",lang(47),": ","<input type='number' class='input size' name='Auto_increment' size='6' value='",h($K["Auto_increment"]),"'>";$Lb=$_POST?$_POST["comments"]:Admin::get()->getSettings()->getParameter("commentsOpened");$Jb=$Lb?"":"hidden";if(support("comment")){echo
checkbox("comments",1,$Lb,lang(46),"editingCommentsClick(this, ".(support("move_col")?7:6).");","jsonly")," ";if(preg_match('~\n~',$K["Comment"]))echo"<textarea name='Comment' rows='2' cols='20'",($Jb?" class='$Jb'":""),">",h($K["Comment"]),"</textarea>";else
echo"<input name='Comment' value='",h($K["Comment"]),"' data-maxlength='",(Connection::get()->isMinVersion("5.5")?2048:60),"' class='input $Jb'>";}echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(112),"'>";}elseif($a!="")echo"<p>";if($a!="")echo"<input type='submit' class='button' name='drop' value='",lang(159),"'>",confirm(lang(210,$a)),"</p>\n";if($fi&&(DIALECT=="sql"||$a=="")){echo"<div class='field-sets'>\n";$gi=preg_match('~RANGE|LIST~',$K["partition_by"]);print_fieldset_start("partition",lang(211),"split",(bool)$K["partition_by"]);echo"<p>",html_select("partition_by",array_merge([""],$fi),$K["partition_by"]),help_script_command("value.replace(/./, 'PARTITION BY \$&')",true),script("qsl('select').onchange = partitionByChange;"),"(<input class='input' name='partition' value='",h($K["partition"]),"'>) ",lang(49),": ","<input type='number' name='partitions' class='input size ",($gi||!$K["partition_by"]?"hidden":""),"' value='",h($K["partitions"]),"'>","</p>\n","<table id='partition-table'",($gi?"":" class='hidden'"),">\n","<thead><tr><th>",lang(212),"</th><th>",lang(51),"</th></tr></thead>\n";foreach($K["partition_names"]as$u=>$X){echo"<tr>","<td><input class='input' name='partition_names[]' value='",h($X),"' autocapitalize='off'>";if($u==count($K["partition_names"])-1)echo
script("qsl('input').oninput = partitionNameChange;");echo"</td>","<td><input class='input' name='partition_values[]' value='",h(isset($K["partition_values"][$u])?$K["partition_values"][$u]:""),"'></td>","</tr>\n";}echo"</table>\n","</p>\n";print_fieldset_end("partition");echo"</div>\n";}echo
input_token(),"</form>\n";}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$af=["PRIMARY","UNIQUE","INDEX"];$R=table_status1($a,true);$Ye=Driver::get()->getIndexAlgorithms($R);$e=Connection::get();$ng=$e->isMariaDB();if(preg_match('~MyISAM|M?aria'.($e->isMinVersion($ng?"10.0.5":"5.6")?'|InnoDB':'').'~i',$R["Engine"]))$af[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.($e->isMinVersion($ng?"10.2.2":"5.7")?'|InnoDB':'').'~i',$R["Engine"]))$af[]="SPATIAL";if($ng&&$e->isMinVersion("11.7")&&preg_match('~MyISAM|InnoDB~i',$R["Engine"]))$af[]="VECTOR";$t=indexes($a);$l=fields($a);$Fi=[];if(DIALECT=="mongo"){$Fi=$t["_id_"];unset($af[0]);unset($t["_id_"]);}$K=$_POST;if($K)Admin::get()->getSettings()->updateParameter("indexOptions",isset($K["options"])?$K["options"]:null);if($_POST&&!$_POST["add"]&&!$_POST["drop_col"]){$Fa=[];foreach($K["indexes"]as$s){$A=$s["name"];if(in_array($s["type"],$af)){$c=[];$Xf=[];$_c=[];$Xe=$Ye?(in_array($s["algorithm"],$Ye)?$s["algorithm"]:first($Ye)):"";$Ze=(support("partial_indexes")?$s["partial"]:"");$O=[];ksort($s["columns"]);foreach($s["columns"]as$u=>$b){if($b!=""){$v=isset($s["lengths"][$u])?$s["lengths"][$u]:null;$yc=isset($s["descs"][$u])?$s["descs"][$u]:null;$O[]=($l[$b]?idf_escape($b):$b).($v?"(".(+$v).")":"").($yc?" DESC":"");$c[]=$b;$Xf[]=($v?:null);$_c[]=$yc;}}$td=$t[$A];if($td){ksort($td["columns"]);ksort($td["lengths"]);ksort($td["descs"]);if($s["type"]==$td["type"]&&array_values($td["columns"])===$c&&(!$td["lengths"]||array_values($td["lengths"])===$Xf)&&array_values($td["descs"])===$_c&&(!$Ye||$td["algorithm"]===$Xe)&&$td["partial"]==$Ze){unset($t[$A]);continue;}}if($c)$Fa[]=[$s["type"],$A,$O,$Xe,$Ze];}}foreach($t
as$A=>$td)$Fa[]=[$td["type"],$A,"DROP"];if(!$Fa)redirect(ME."table=".urlencode($a));queries_redirect(ME."table=".urlencode($a),lang(213),alter_indexes($a,$Fa));}page_header(lang(166),["table"=>$a,lang(166)],h($a));$Ld=array_keys($l);if($_POST["add"]){foreach($K["indexes"]as$u=>$s){if($s["columns"][count($s["columns"])]!="")$K["indexes"][$u]["columns"][]="";}$s=end($K["indexes"]);if($s["type"]||array_filter($s["columns"],'strlen'))$K["indexes"][]=["columns"=>[1=>""]];}if(!$K){foreach($t
as$u=>$s){$t[$u]["name"]=$u;$t[$u]["columns"][]="";}$t[]=["columns"=>[1=>""]];$K["indexes"]=$t;}$Xf=(DIALECT=="sql"||DIALECT=="mssql");$fk=$_POST?$_POST["options"]:Admin::get()->getSettings()->getParameter("indexOptions");echo"<form action='' method='post'>\n","<div class='scrollable'>\n","<table class='nowrap'>\n","<thead><tr>","<th id='label-type'>",lang(214),"</th>";$Fh="class='idxopts".($fk?"":" hidden")."'";if(count($Ye)>1)echo"<th id='label-method' $Fh>",lang(215),doc_link(['sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'ha-and-performance/optimization-and-tuning/optimization-and-indexes/storage-engine-index-types',]),"</th>";echo"<th><input type='submit' class='button invisible'>",lang(52).($Xf?"<span $Fh> (".lang(53).")</span>":"");if($Xf||support("descidx"))echo
checkbox("options",1,$fk,lang(96),"indexOptionsShow(this.checked)","jsonly")."\n";echo"</th>","<th id='label-name'>",lang(216),"</th>";if(support("partial_indexes"))echo"<th id='label-condition' $Fh>",lang(54),"</th>";echo"<th>","<button name='add[0]' value='1' title='",lang(97),"' class='button light hidden'>",icon_solo("add"),"</button>","</th>","</tr></thead>\n";if($Fi){echo"<tr><td>PRIMARY<td>";foreach($Fi["columns"]as$b)echo
select_input(" disabled",$Ld,$b),"<label><input type='checkbox' disabled>".lang(62)."</label> ";echo"<td><td>\n";}$xf=1;foreach($K["indexes"]as$s){if(!$_POST["drop_col"]||$xf!=key($_POST["drop_col"])){echo"<tr><td>",html_select("indexes[$xf][type]",[-1=>""]+$af,$s["type"],($xf==count($K["indexes"])?"indexesAddRow.call(this);":""),"label-type"),"</td>";if(count($Ye)>1)echo"<td $Fh>",html_select("indexes[$xf][algorithm]",array_merge([""],$Ye),$s['algorithm'],"label-method"),"</td>";echo"<td>";ksort($s["columns"]);$q=1;foreach($s["columns"]as$u=>$b){echo"<span>".select_input(" name='indexes[$xf][columns][$q]' title='".lang(43)."'",($l&&($b==""||$l[$b])?array_combine($Ld,$Ld):[]),$b,"partial(".($q==count($s["columns"])?"indexesAddColumn":"indexesChangeColumn").", '".js_escape(DIALECT=="sql"?"":$_GET["indexes"]."_")."')"),"<span $Fh>";if($Xf)echo"<input type='number' name='indexes[$xf][lengths][$q]' class='input size' value='".(h(isset($s["lengths"][$u])?$s["lengths"][$u]:"")),"' title='".lang(95),"'>";if(support("descidx"))echo
checkbox("indexes[$xf][descs][$q]",1,isset($s["descs"][$u])?$s["descs"][$u]:false,lang(62));echo"</span> </span>";$q++;}echo"</td>","<td><input name='indexes[$xf][name]' value='",h($s["name"]),"' class='input' autocapitalize='off' aria-labelledby='label-name'></td>\n";if(support("partial_indexes"))echo"<td $Fh><input name='indexes[$xf][partial]' value='".h($s["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>","<button name='drop_col[$xf]' value='1' title='",h(lang(58)),"' class='button light'>",icon_solo("remove"),"</button>",script("qsl('button').onclick = onRemoveIndexRowClick;"),"</td>\n";}$xf++;}echo"</table>\n","</div>\n","<p>","<input type='submit' class='button default' value='",lang(112),"'>",input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["database"])){$K=$_POST;if($_POST&&!isset($_POST["add_x"])){$A=trim($K["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),lang(217),drop_databases([DB]));}elseif(DB!==$A){if(DB!=""){$_GET["db"]=$A;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".urlencode($A),lang(218),rename_database($A,$K["collation"]));}else{$g=explode("\n",str_replace("\r","",$A));$Bk=true;$Of="";foreach($g
as$h){if(count($g)==1||$h!=""){if(!create_database($h,$K["collation"]))$Bk=false;$Of=$h;}}restart_session();set_session("dbs",null);queries_redirect(ME."db=".urlencode($Of),lang(219),$Bk);}}else{if(!$K["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($A).(preg_match('~^[a-z0-9_]+$~i',$K["collation"])?" COLLATE $K[collation]":""),substr(ME,0,-1),lang(220));}}if(DB!="")page_header(lang(69).": ".h(DB),[lang(69)]);else
page_header(lang(75),[lang(75)]);$A=DB;if($_POST)$A=$K["name"];elseif(DB!="")$K["collation"]=db_collation(DB,collations());elseif(DIALECT=="sql"){foreach(get_vals("SHOW GRANTS")as$qe){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$qe,$y)&&$y[1]){$A=stripcslashes(idf_unescape("`$y[2]`"));break;}}}$Ab=Admin::get()->getCollations($K["collation"]?[$K["collation"]]:[]);echo"<form action='' method='post'>\n","<p>";if($_POST["add_x"]||strpos($A,"\n"))echo"<textarea id='name' name='name' rows='10' cols='40'>",h($A),"</textarea><br>\n";else
echo"<input class='input' name='name' id='name' value='",h($A),"' data-maxlength='64' autocapitalize='off' autofocus>\n";if($Ab)echo
html_select("collation",[""=>"(".lang(90).")"]+$Ab,$K["collation"]),doc_link(['sql'=>"charset-charsets.html",'mariadb'=>"reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations",]),"\n";echo"<input type='submit' class='button default' value='",lang(112),"'>\n";if(DB!="")echo"<input type='submit' class='button' name='drop' value='".lang(159)."'>".confirm(lang(210,DB))."\n";elseif(!$_POST["add_x"]&&$_GET["db"]=="")echo"<button name='add_x' value='1' title='",h(lang(97)),"' class='button light'>",icon_solo("add"),"</button>\n";echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["call"])){$na=$_GET["name"]?:$_GET["call"];page_header(lang(221).": ".h($na),[lang(221)]);$sj=routine($_GET["call"],(isset($_GET["callf"])?"FUNCTION":"PROCEDURE"));$Ve=[];$Th=[];foreach($sj["fields"]as$q=>$k){if(substr($k["inout"],-3)=="OUT"&&DIALECT=='sql')$Th[$q]="@".idf_escape($k["field"])." AS ".idf_escape($k["field"]);if(!$k["inout"]||substr($k["inout"],0,2)=="IN")$Ve[]=$q;}if($_POST){$ib=[];foreach($sj["fields"]as$u=>$k){$X="";if(in_array($u,$Ve)){$X=process_input($k);if($X===false)$X="''";if(isset($Th[$u]))Connection::get()->query("SET @".idf_escape($k["field"])." = $X");}if(isset($Th[$u]))$ib[]="@".idf_escape($k["field"]);elseif(in_array($u,$Ve))$ib[]=$X;}$H=(isset($_GET["callf"])?"SELECT ":"CALL ").($sj["returns"]&&$sj["returns"]["type"]=="record"?"* FROM ":"").table($na)."(".implode(", ",$ib).")";$sk=microtime(true);$I=Connection::get()->multiQuery($H);$ya=Connection::get()->getAffectedRows();echo
Admin::get()->formatSelectQuery($H,$sk,!$I);if(!$I)echo"<p class='error'>".error()."\n";else{$Rb=connect();if($Rb)$Rb->selectDatabase(DB);do{$I=Connection::get()->storeResult();if(is_object($I))print_select_result($I,$Rb);else
echo"<p class='message'>".lang(222,$ya)." <span class='time'>".@date("H:i:s")."</span>\n";}while(Connection::get()->nextResult());if($Th)print_select_result(Connection::get()->query("SELECT ".implode(", ",$Th)));}}echo"<form action='' method='post'>\n";if($Ve){echo"<table class='box'>\n";foreach($Ve
as$u){$k=$sj["fields"][$u];$A=$k["field"];echo"<tr><th>".Admin::get()->getFieldName($k);$Y=isset($_POST["fields"][$A])?$_POST["fields"][$A]:"";if($Y!=""){if($k["type"]=="set")$Y=implode(",",$Y);}input($k,$Y,(string)(isset($_POST["function"][$A])?$_POST["function"][$A]:""));echo"\n";}echo"</table>\n";}echo"<p>\n","<input type='submit' class='button' value='",lang(221),"'>\n",input_token(),"</p>\n","</form>\n";$Ib=$sj["comment"];if($Ib!==null&&$Ib!==""){$Ib=h(trim($sj["comment"],"\n"));if(preg_match('~^ +~',$Ib,$z)){preg_match_all("~^($z[0]|$)~m",$Ib,$bg);if(count($bg[0])==substr_count($Ib,"\n"))$Ib=preg_replace("~^($z[0])~m","",$Ib);}$Ib=preg_replace('~(^|[^\n]\n)(Description|Parameters|Example)\n~',"$1\n<strong>$2</strong>\n",$Ib);echo"<pre class='comment'>$Ib</pre>\n";}}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$A=$_GET["name"];$K=$_POST;if($_POST&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$K["source"]=array_filter($K["source"],'strlen');ksort($K["source"]);$dl=[];foreach($K["source"]as$u=>$X)$dl[$u]=$K["target"][$u];$K["target"]=$dl;}if(DIALECT=="sqlite")$I=recreate_table($a,$a,[],[],[" $A"=>($K["drop"]?"":" ".format_foreign_key($K))]);else{$Fa="ALTER TABLE ".table($a);$I=($A==""||queries("$Fa DROP ".(DIALECT=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($A)));if(!$K["drop"])$I=queries("$Fa ADD".format_foreign_key($K));}queries_redirect(ME."table=".urlencode($a),($K["drop"]?lang(223):($A!=""?lang(224):lang(225))),(bool)$I);if(!$K["drop"])Admin::get()->addError(lang(226));}page_header(lang(227).": ".h($a),["table"=>$a,lang(227)]);if($_POST){ksort($K["source"]);if($_POST["change"]||$_POST["change-js"])$K["target"]=[];else$K["source"][]="";}elseif($A!=""){$ae=foreign_keys($a);$K=$ae[$A];$K["source"][]="";}else{$K["table"]=$a;$K["source"]=[""];}echo"<form action='' method='post'>\n";$lk=array_keys(fields($a));if($K["db"]!="")Connection::get()->selectDatabase($K["db"]);if($K["ns"]!=""){$Qh=get_schema();set_schema($K["ns"]);}$aj=array_keys(array_filter(table_status('',true),'AdminNeo\fk_support'));$dl=array_keys(fields(in_array($K["table"],$aj)?$K["table"]:reset($aj)));$yh="this.form['change-js'].value = '1'; this.form.submit();";echo"<p>","<span id='label-table'>",lang(228),":</span> ",html_select("table",$aj,$K["table"],$yh,"label-table");if(DIALECT!="sqlite"){$oc=[];foreach(Admin::get()->getDatabases()as$h){if(!information_schema($h))$oc[]=$h;}echo"<span id='label-db'>",lang(229),":</span> ",html_select("db",$oc,$K["db"]!=""?$K["db"]:$_GET["db"],$yh,"label-db");}echo
input_hidden("change-js"),"<noscript><input type='submit' class='button' name='change' value='",lang(230),"'></noscript>","</p>\n","<table>","<thead><tr><th id='label-source'>",lang(167),"<th id='label-target'>",lang(168),"</thead>\n";$xf=0;foreach($K["source"]as$u=>$X){echo"<tr>","<td>".html_select("source[".(+$u)."]",[-1=>""]+$lk,$X,($xf==count($K["source"])-1?"foreignAddRow.call(this);":""),"label-source"),"<td>".html_select("target[".(+$u)."]",$dl,isset($K["target"][$u])?$K["target"][$u]:null,"","label-target");$xf++;}echo"</table>\n","<noscript><p><input type='submit' class='button' name='add' value='",lang(231),"'></p></noscript>","<p>\n","<span id='label-delete'>".lang(92),":</span> ",html_select("on_delete",[-1=>""]+Driver::get()->getOnActions(),$K["on_delete"],"","label-delete"),"<span id='label-update'>".lang(91),":</span> ",html_select("on_update",[-1=>""]+Driver::get()->getOnActions(),$K["on_update"],"","label-update");if(DRIVER=='pgsql')echo
html_select("deferrable",['NOT DEFERRABLE','DEFERRABLE','DEFERRABLE INITIALLY DEFERRED'],$K["deferrable"]);echo
doc_link(['sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"architecture/server-constraints/foreign-key-constraints",]),"</p>\n<p>","<input type='submit' class='button default' value='",lang(112),"'>";if($A!="")echo"<input type='submit' class='button' name='drop' value='",lang(159),"'>",confirm(lang(210,$A));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["view"])){$a=$_GET["view"];$K=$_POST;$Rh="VIEW";if(DIALECT=="pgsql"&&$a!=""){$uk=table_status1($a);$Rh=strtoupper($uk["Engine"]);}if($_POST){$A=trim($K["name"]);$Ja=" AS\n$K[select]";$lg=ME."table=".urlencode($A);$_=lang(232);$U=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$A&&DIALECT!="sqlite"&&$U=="VIEW"&&$Rh=="VIEW")query_redirect((DIALECT=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($A).$Ja,$lg,$_);else{$fl=$A."_adminneo_".uniqid();drop_create("DROP $Rh ".table($a),"CREATE $U ".table($A).$Ja,"DROP $U ".table($A),"CREATE $U ".table($fl).$Ja,"DROP $U ".table($fl),($_POST["drop"]?substr(ME,0,-1):$lg),lang(233),$_,lang(234),$a,$A);}}if(!$_POST&&$a!=""){$K=view($a);$K["name"]=$a;$K["materialized"]=($Rh!="VIEW");if($j=error())Admin::get()->addError($j);}if($a!="")page_header(lang(36).": ".h($a),["table"=>$a,lang(36)]);else
page_header(lang(235),[lang(235)]);echo"<form action='' method='post'>\n","<p>",lang(216),":","<input class='input' name='name' value='",h($K["name"]),"' data-maxlength='64' autocapitalize='off'>\n";if(support("materializedview"))echo
checkbox("materialized",1,$K["materialized"],lang(160));echo"</p>\n<p>";textarea("select",$K["select"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(112),"'>\n";if($a!="")echo"<input type='submit' class='button' name='drop' value='",lang(159),"'>\n",confirm(lang(210,$a));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["event"])){$ea=$_GET["event"];$kf=["YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND"];$vk=["ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE"];$K=$_POST;if($_POST){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($ea),substr(ME,0,-1),lang(236));elseif(in_array($K["INTERVAL_FIELD"],$kf)&&isset($vk[$K["STATUS"]])){$Cj="\nON SCHEDULE ".($K["INTERVAL_VALUE"]?"EVERY ".q($K["INTERVAL_VALUE"])." $K[INTERVAL_FIELD]".($K["STARTS"]?" STARTS ".q($K["STARTS"]):"").($K["ENDS"]?" ENDS ".q($K["ENDS"]):""):"AT ".q($K["STARTS"]))." ON COMPLETION".($K["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($ea!=""?lang(237):lang(238)),(bool)queries(($ea!=""?"ALTER EVENT ".idf_escape($ea).$Cj.($ea!=$K["EVENT_NAME"]?"\nRENAME TO ".idf_escape($K["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($K["EVENT_NAME"]).$Cj)."\n".$vk[$K["STATUS"]]." COMMENT ".q($K["EVENT_COMMENT"]).rtrim(" DO\n$K[EVENT_DEFINITION]",";").";"));}}if($ea!="")page_header(lang(239).": ".h($ea),[lang(239)]);else
page_header(lang(240),[lang(240)]);if(!$K&&$ea!=""){$L=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($ea));$K=reset($L);}echo"<form action='' method='post'>\n","<table class='box box-light'>\n","<tr><th>",lang(216),"</th><td>","<input class='input' name='EVENT_NAME' value='",h($K["EVENT_NAME"]),"' data-maxlength='64' autocapitalize='off'>","</td></tr>\n","<tr><th title='datetime'>",lang(241),"</th><td>","<input class='input' name='STARTS' value='",h("$K[EXECUTE_AT]$K[STARTS]"),"'>","</td></tr>\n","<tr><th title='datetime'>",lang(242),"</th><td>","<input class='input' name='ENDS' value='",h($K["ENDS"]),"'>","</td></tr>\n","<tr><th>",lang(243),"</th><td>","<input type='number' name='INTERVAL_VALUE' value='",h($K["INTERVAL_VALUE"]),"' class='input size'> ",html_select("INTERVAL_FIELD",$kf,$K["INTERVAL_FIELD"]),"</td></tr>\n","<tr><th>",lang(151),"</th><td>",html_select("STATUS",$vk,$K["STATUS"]),"</td></tr>\n","<tr><th>",lang(46),"</th><td>","<input class='input' name='EVENT_COMMENT' value='",h($K["EVENT_COMMENT"]),"' data-maxlength='64'>","</td></tr>\n","<tr><th></th><td>",checkbox("ON_COMPLETION","PRESERVE",$K["ON_COMPLETION"]=="PRESERVE",lang(244)),"</td></tr>\n","</table>\n","<p>";textarea("EVENT_DEFINITION",$K["EVENT_DEFINITION"]);echo"</p>\n","<p>","<input type='submit' class='button default' value='",lang(112),"'>";if($ea!="")echo"<input type='submit' class='button' name='drop' value='",lang(159),"'>",confirm(lang(210,$ea));echo"</p>\n",input_token(),"</form>\n";}elseif(isset($_GET["procedure"])){$na=($_GET["name"]?:$_GET["procedure"]);$sj=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$K=$_POST;$K["fields"]=(array)$K["fields"];if($_POST&&!process_fields($K["fields"])){foreach($K["fields"]as$u=>$k){if($k["field"]=="")unset($K["fields"][$u]);}$th=routine_id($na,routine($_GET["procedure"],$sj));$ch=routine_id($K["name"],$K);$Zb=create_routine($sj,$K);$lg=substr(ME,0,-1);$_=lang(245);if(!$_POST["drop"]&&$th==$ch&&(DIALECT!="sql"||Connection::get()->isMariaDB()))query_redirect(substr_replace($Zb,' OR REPLACE',6,0),$lg,$_);else{$fl="$K[name]_adminer_".uniqid();drop_create("DROP $sj $th",$Zb,"DROP $sj $ch",create_routine($sj,["name"=>$fl]+$K),"DROP $sj ".routine_id($fl,$K),$lg,lang(246),$_,lang(247),$na,$K["name"]);}}if($na!=""){$T=isset($_GET["function"])?lang(248):lang(249);page_header($T.": ".h($na),[$T]);}else{$T=isset($_GET["function"])?lang(250):lang(251);page_header($T,[$T]);}if(!$_POST){if($na=="")$K["language"]="sql";else{$K=routine($_GET["procedure"],$sj);$K["name"]=$na;}}$nb=get_vals("SHOW CHARACTER SET");sort($nb);$tj=routine_languages();echo"<form action='' method='post' id='form'>\n","<p>",lang(216),": ","<input class='input' name='name' value='",h($K["name"]),"' data-maxlength='64' autocapitalize='off'>";if($tj)echo"<span id='label-language'>",lang(9),":</span> ",html_select("language",$tj,$K["language"],"","label-language");echo"<input type='submit' class='button default' value='",lang(112),"'>","</p>\n","<div class='scrollable'>\n","<table class='nowrap' id='edit-fields'>\n";edit_fields($K["fields"],$nb,$sj);if(isset($_GET["function"])){echo"<tbody><tr>";if(support("move_col"))echo"<th></th>";echo"<th>",lang(252),"</th>";edit_type("returns",(array)$K["returns"],$nb,[],(DIALECT=="pgsql"?["void","trigger"]:[]));echo"<td></td>","</tr></tbody>\n";}echo"</table>\n",script("initFieldsEditing(gid('edit-fields'));");if(support("move_col"))echo
script("initSortable('#edit-fields tbody');");echo"</div>\n","<p>";textarea("definition",$K["definition"],20);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(112),"'>";if($na!="")echo"<input type='submit' class='button' name='drop' value='",lang(159),"'>",confirm(lang(210,$na));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["check"])){$a=$_GET["check"];$A=$_GET["name"];$K=$_POST;if($K){if(DIALECT=="sqlite")$Bk=recreate_table($a,$a,[],[],[],"",[],"$A",($K["drop"]?"":$K["clause"]));else{$Bk=($A==""||queries("ALTER TABLE ".table($a)." DROP CONSTRAINT ".idf_escape($A)));if(!$K["drop"])$Bk=(bool)queries("ALTER TABLE ".table($a)." ADD".($K["name"]!=""?" CONSTRAINT ".idf_escape($K["name"]):"")." CHECK ($K[clause])");}queries_redirect(ME."table=".urlencode($a),($K["drop"]?lang(253):($A!=""?lang(254):lang(255))),$Bk);}page_header(($A!=""?lang(256).": ".h($A):lang(172)),["table"=>$a]);if(!$K){$sb=Driver::get()->checkConstraints($a);$K=["name"=>$A,"clause"=>$sb[$A]];}echo"<form action='' method='post'>\n","<p>";if(DIALECT!="sqlite")echo
lang(216).': <input name="name" value="'.h($K["name"]).'" class="input" data-maxlength="64" autocapitalize="off"> ';echo
doc_link(['sql'=>"create-table-check-constraints.html",'mariadb'=>"reference/sql-statements/data-definition/constraint",],"?"),"</p>\n<p>";textarea("clause",$K["clause"]);echo"</p>\n<p>","<input type='submit' class='button default' value='",lang(112),"'>";if($A!="")echo"<input type='submit' class='button' name='drop' value='",lang(159),"'>",confirm(lang(210,$A));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$A=isset($_GET["name"])?$_GET["name"]:"";$Bl=trigger_options();$K=trigger($A,$a)+["Trigger"=>$a."_bi"];if($_POST){if(in_array($_POST["Timing"],$Bl["Timing"])&&in_array($_POST["Event"],$Bl["Event"])&&in_array($_POST["Type"],$Bl["Type"])){$wh=" ON ".table($a);$Qc="DROP TRIGGER ".idf_escape($A).(DIALECT=="pgsql"?$wh:"");$lg=ME."table=".urlencode($a);if($_POST["drop"])query_redirect($Qc,$lg,lang(257));else{if($A!="")queries($Qc);queries_redirect($lg,($A!=""?lang(258):lang(259)),(bool)queries(create_trigger($wh,$_POST)));if($A!="")queries(create_trigger($wh,$K+["Type"=>reset($Bl["Type"])]));}}$K=$_POST;}if($A!="")page_header(lang(260).": ".h($A),["table"=>$a,h($A)]);else
page_header(lang(261),["table"=>$a,lang(261)]);echo"<form action='' method='post' id='form'>\n","<table class='box box-light'>\n","<tr><th id='label-time'>",lang(262),"</th><td>",html_select("Timing",$Bl["Timing"],$K["Timing"],"triggerChange(/^".preg_quote($a,"/")."_[ba][iud]$/, '".js_escape($a)."', this.form);","label-time"),"</td></tr>\n","<tr><th id='label-event'>",lang(263),"</th><td>",html_select("Event",$Bl["Event"],$K["Event"],"this.form['Timing'].onchange();","label-event");if(in_array("UPDATE OF",$Bl["Event"]))echo" <input name='Of' value='".h($K["Of"])."' class='input hidden'>";echo"</td></tr>\n","<tr><th id='label-type'>",lang(44),"</th><td>",html_select("Type",$Bl["Type"],$K["Type"],"","label-type"),"</td></tr>\n","</table>\n","<p>",lang(216),"<input class='input' name='Trigger' value='",h($K["Trigger"]),"' data-maxlength='64' autocapitalize='off'>","</p>\n",script("gid('form')['Timing'].onchange();"),"<p>";textarea("Statement",$K["Statement"]);echo"</p>\n","<p>","<input type='submit' class='button default' value='",lang(112),"'>";if($A!="")echo"<input type='submit' class='button' name='drop' value='",lang(159),"'>",confirm(lang(210,$A));echo"</p>\n",input_token(),"</form>\n";}elseif(isset($_GET["user"])){$pa=$_GET["user"];$Ji=[""=>["All privileges"=>""]];foreach(get_rows("SHOW PRIVILEGES")as$K){foreach(explode(",",($K["Privilege"]=="Grant option"?"":$K["Context"]))as$Vb)$Ji[$Vb=="File access on server"?"Server Admin":$Vb][$K["Privilege"]]=$K["Comment"];}unset($Ji["Server Admin"]["Usage"]);foreach($Ji["Tables"]as$u=>$X)unset($Ji["Databases"][$u]);$bh=[];if($_POST){foreach($_POST["objects"]as$u=>$X)$bh[$X]=(array)$bh[$X]+(array)$_POST["grants"][$u];}$se=[];if(isset($_GET["host"])&&($I=Connection::get()->query("SHOW GRANTS FOR ".q($pa)."@".q($_GET["host"])))){while($K=$I->fetchRow()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$K[0],$y)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$y[1],$z,PREG_SET_ORDER)){foreach($z
as$X){if($X[1]!="USAGE")$se["$y[2]$X[2]"][$X[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$K[0]))$se["$y[2]$X[2]"]["GRANT OPTION"]=true;}}}}$si=!Connection::get()->isMariaDB()&&Connection::get()->isMinVersion("8");if($_POST){$vh=(isset($_GET["host"])?q($pa)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $vh",ME."privileges=",lang(264));else{$eh=q($_POST["user"])."@".q($_POST["host"]);$li=$_POST["pass"];$cc=false;$I=true;if($vh!=$eh){$cc=(bool)queries("CREATE USER $eh IDENTIFIED BY ".($_POST["hashed"]?"PASSWORD ":"").q($li));$I=$cc;}elseif($li!="")$I=(bool)queries("SET PASSWORD FOR $eh = ".($si||$_POST["hashed"]?q($li):"PASSWORD(".q($li).")"));if($I){$pj=[];foreach($bh
as$mh=>$qe){if(isset($_GET["grant"]))$qe=array_filter($qe);$qe=array_keys($qe);if(isset($_GET["grant"]))$pj=array_diff(array_keys(array_filter($bh[$mh],'strlen')),$qe);elseif($vh==$eh){$sh=array_keys((array)$se[$mh]);$pj=array_diff($sh,$qe);$qe=array_diff($qe,$sh);unset($se[$mh]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$mh,$y)&&(!grant(false,$pj,$y[2],$y[1],$eh)||!grant(true,$qe,$y[2],$y[1],$eh))){$I=false;break;}}}if($I&&isset($_GET["host"])){if($vh!=$eh)queries("DROP USER $vh");elseif(!isset($_GET["grant"])){foreach($se
as$mh=>$pj){if(preg_match('~^(.+)(\(.*\))?$~U',$mh,$y))grant(false,array_keys($pj),$y[2],$y[1],$eh);}}}queries_redirect(ME."privileges=",(isset($_GET["host"])?lang(265):lang(266)),$I);if($cc)Connection::get()->query("DROP USER $eh");}}$T=isset($_GET["host"])?lang(28).": ".h("$pa@$_GET[host]"):lang(182);$ql=isset($_GET["host"])?h($pa):lang(182);page_header($T,["privileges"=>['',lang(72)],$ql]);if($_POST){$K=$_POST;$se=$bh;}else{$K=$_GET+["host"=>Connection::get()->getValue("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)")];if($se)$se[".*"]=[];elseif(DB!="")$se[idf_escape(addcslashes(DB,"%_\\")).".*"]=[];else$se["*.* "]=[];}echo"<form action='' method='post'>\n","<table class='box box-light'>\n","<tr><th>",lang(5),"</th>","<td><input class='input' name='host' data-maxlength='60' value='",h($K["host"]),"' autocapitalize='off'></td>\n","<tr><th>",lang(28),"</th>","<td><input class='input' name='user' data-maxlength='80' value='",h($K["user"]),"' autocapitalize='off'></td>\n",'<tr><th>',lang(29),"</th>","<td><input class='input' name='pass' id='pass' value='",h($K["pass"]),"' autocomplete='new-password'>";if(!$si)echo
checkbox("hashed",1,$K["hashed"],lang(267),"typePassword(this.form['pass'], this.checked);");echo"</td>\n";if(!$K["hashed"])echo
script("typePassword(gid('pass'));");echo"</table>\n","<div class='scrollable'><table class='checkable'>\n","<thead><tr><th colspan='2'>".lang(72).doc_link(['sql'=>"grant.html#priv_level","mariadb"=>"reference/sql-statements/account-management-sql-statements/grant#privilege-levels"])."</th>";$q=0;foreach($se
as$mh=>$qe){echo"<th>";if($mh=="*.*")echo"*.*",input_hidden("objects[$q]","*.*");else
echo"<input class='input' name='objects[$q]' value='".h(trim($mh))."' size='10' autocapitalize='off'>";echo"</th>";$q++;}echo"</tr></thead>\n";foreach([""=>"","Server Admin"=>lang(5),"Databases"=>lang(30),"Tables"=>lang(8),"Procedures"=>lang(268),]as$Vb=>$yc){foreach((array)$Ji[$Vb]as$Ii=>$Ib){echo"<tr>";if($yc)echo"<td>$yc</td>";echo"<td".(!$yc?" colspan='2'":"").' lang="en" title="'.h($Ib).'">'.h($Ii)."</td>";$q=0;foreach($se
as$mh=>$qe){$A="'grants[$q][".h(strtoupper($Ii))."]'";$Y=$qe[strtoupper($Ii)];$Ni=strpos($mh,"@")!==false;$ah=$mh==".*";$Ba=$Ii=="All privileges";$re=$Ii=="Grant option";if($mh=="*.*"&&$Ii=="Proxy")echo"<td></td>";elseif($Ni&&$Ii!="Proxy"&&!$re)echo"<td></td>";elseif($Vb=="Server Admin"&&$mh!=(isset($se["*.*"])?"*.*":".*")&&!(($Ni||$ah)&&$Ii=="Proxy"))echo"<td></td>";elseif(isset($_GET["grant"]))echo"<td><select name=$A>"."<option></option>"."<option value='1'".($Y?" selected":"").">".lang(269)."</option>"."<option value='0'".($Y=="0"?" selected":"").">".lang(270)."</option>"."</select></td>";else{echo"<td class='center'><label class='block'>","<input type='checkbox' name=$A value='1'".($Y?" checked":"").($Ba?" id='grants-$q-all'":(!$re?" class='grants-$q'":"")).">";if($Ba)echo
script("qsl('input').onclick = function () { if (this.checked) formUncheckAll('.grants-$q'); };");elseif(!$re)echo
script("qsl('input').onclick = function () { if (this.checked) formUncheck('grants-$q-all'); };");echo"</label>";}$q++;}echo"</tr>";}}echo"</table></div>\n","<p>","<input type='submit' class='button default' value='",lang(112),"'>\n";if(isset($_GET["host"]))echo"<input type='submit' class='button' name='drop' value='",lang(159),"'>\n",confirm(lang(210,"$pa@$_GET[host]"));echo
input_token(),"</p>\n","</form>\n";}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST){$Gf=0;foreach((array)$_POST["kill"]as$X){if(kill_process($X))$Gf++;}queries_redirect(ME."processlist=",lang(271,$Gf),$Gf||!$_POST["kill"]);}}page_header(lang(149),[lang(149)]);echo"<form action='' method='post'>\n","<div class='scrollable'>\n","<table class='nowrap checkable'>\n";$q=-1;foreach(process_list()as$q=>$K){if(!$q){echo"<thead><tr lang='en'>".(support("kill")?"<th>":"");foreach($K
as$u=>$X)echo"<th>$u".doc_link(['sql'=>"show-processlist.html#processlist_".strtolower($u),'mariadb'=>"reference/sql-statements/administrative-sql-statements/show/show-processlist",]);echo"</thead>\n","<tbody>\n";}echo"<tr>".(support("kill")?"<td>".checkbox("kill[]",$K[DIALECT=="sql"?"Id":"pid"],0):"");foreach($K
as$u=>$X)echo"<td>".($X!=""&&((DIALECT=="sql"&&$u=="Info"&&preg_match("~Query|Killed~",$K["Command"]))||(DIALECT=="pgsql"&&$u=="query")||(DIALECT=="oracle"&&$u=="sql_text"))?"<code class='jush-".DIALECT."'>".truncate_utf8($X,100).'</code> <a href="'.h(ME.($K["db"]!=""?"db=".urlencode($K["db"])."&":"")."sql=".urlencode($X)).'">'.icon("edit").lang(272).'</a>':h($X));echo"\n";}if($q>=0)echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});");echo"</table>\n","</div>\n","<p>";if(support("kill"))echo($q+1)."/".lang(273,max_connections()),"<p><input type='submit' class='button' value='".lang(274)."'>\n";echo
input_token(),"</p>\n","</form>\n",script("tableCheck();");}elseif(isset($_GET["select"])){$a=$_GET["select"];$R=table_status1($a);$t=indexes($a);$l=fields($a);$ae=column_foreign_keys($a);$oh=$R["Oid"];$qj=[];$c=[];$Ij=[];$Hh=[];$jl=null;foreach($l
as$u=>$k){$A=Admin::get()->getFieldName($k);$Wg=html_entity_decode(strip_tags($A),ENT_QUOTES);if(isset($k["privileges"]["select"])&&$A!=""){$c[$u]=$Wg;if(is_shortable($k))$jl=Admin::get()->processSelectionLength();}if(isset($k["privileges"]["where"])&&$A!="")$Ij[$u]=$Wg;if(isset($k["privileges"]["order"])&&$A!="")$Hh[$u]=$Wg;$qj+=$k["privileges"];}list($M,$te)=Admin::get()->processSelectionColumns($c,$t);$M=array_unique($M);$te=array_unique($te);$qf=count($te)<count($M);$Z=Admin::get()->processSelectionSearch($l,$t);$D=Admin::get()->processSelectionOrder($l,$t);$w=Admin::get()->processSelectionLimit();if($_GET["modify"]&&!Admin::get()->isDataEditAllowed())redirect(ME."select=".urlencode($a));if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$Ml=>$K){$Ja=convert_field($l[key($K)]);$M=[$Ja?:idf_escape(key($K))];$Z[]=where_check($Ml,$l);$J=Driver::get()->select($a,$M,$Z,$M);if($J)echo
first($J->fetchRow());}exit;}$Fi=$Pl=[];foreach($t
as$s){if($s["type"]=="PRIMARY"){$Fi=array_flip($s["columns"]);$Pl=($M?$Fi:[]);foreach($Pl
as$u=>$X){if(in_array(idf_escape($u),$M))unset($Pl[$u]);}break;}}if($oh&&!$Fi){$Fi=$Pl=[$oh=>0];$t[]=["type"=>"PRIMARY","columns"=>[$oh]];}$P=Admin::get()->getSettings();if($_POST){$xm=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$sb=[];foreach($_POST["check"]as$ob)$sb[]=where_check($ob,$l);$xm[]="((".implode(") OR (",$sb)."))";}$xm=($xm?"\nWHERE ".implode(" AND ",$xm):"");if($_POST["export"]){$P->updateParameters(["exportFormat"=>$_POST["format"],"exportOutput"=>$_POST["output"],]);dump_headers($a);Admin::get()->dumpTable($a,"");$ie=($M?implode(", ",$M):"*").convert_fields($c,$l,$M)."\nFROM ".table($a);$we=($te&&$qf?"\nGROUP BY ".implode(", ",$te):"").($D?"\nORDER BY ".implode(", ",$D):"");if(!is_array($_POST["check"])||$Fi)$H="SELECT $ie$xm$we";else{$Jl=[];foreach($_POST["check"]as$X)$Jl[]="(SELECT".limit($ie,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$l).$we,1).")";$H=implode(" UNION ALL ",$Jl);}Admin::get()->dumpData($a,"table",$H);exit;}if($_POST["save"]||$_POST["delete"]){$I=true;$ya=0;$O=[];if(!$_POST["delete"]){$Qj=array_keys($_POST["fields"]+$_POST["function"]);foreach($Qj
as$A){$X=process_input($l[$A]);if($X!==null&&($_POST["clone"]||$X!==false))$O[idf_escape($A)]=($X!==false?$X:idf_escape($A));}}if($_POST["delete"]||$O){if($_POST["clone"])$H="INTO ".table($a)." (".implode(", ",array_keys($O)).")\nSELECT ".implode(", ",$O)."\nFROM ".table($a);if($_POST["all"]||($Fi&&is_array($_POST["check"]))||$qf){$I=($_POST["delete"]?Driver::get()->delete($a,$xm):($_POST["clone"]?queries("INSERT $H$xm".Driver::get()->getInsertReturningSql($a)):Driver::get()->update($a,$O,$xm)));$ya=Connection::get()->getAffectedRows();if(is_object($I))$ya+=$I->getRowsCount();}else{foreach((array)$_POST["check"]as$X){$wm="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$l);$I=($_POST["delete"]?Driver::get()->delete($a,$wm,1):($_POST["clone"]?queries("INSERT".limit1($a,$H,$wm)):Driver::get()->update($a,$O,$wm,1)));if(!$I)break;$ya+=Connection::get()->getAffectedRows();}}}$_=lang(275,$ya);if($_POST["clone"]&&$I&&$ya==1){$Pf=last_id($I);if($Pf)$_=lang(204," $Pf");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$_,(bool)$I);if(!$_POST["delete"]){$ad=array_filter($l,function($k){return!(isset($k["generated"])?$k["generated"]:null);});edit_form($a,$ad,(array)$_POST["fields"],!$_POST["clone"]);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])Admin::get()->addError(lang(276));else{$Bk=true;$ya=0;foreach($_POST["val"]as$Ml=>$K){$O=[];foreach($K
as$u=>$X){$u=bracket_escape($u,true);$O[idf_escape($u)]=(preg_match('~char|text~',$l[$u]["type"])||$X!=""?Admin::get()->processFieldInput($l[$u],$X):"NULL");}$Bk=(bool)Driver::get()->update($a,$O," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($Ml,$l),($qf||$Fi?0:1)," ");if(!$Bk)break;$ya+=Connection::get()->getAffectedRows();}queries_redirect(remove_from_uri(),lang(275,$ya),$Bk);}}elseif(!is_string($m=get_file("csv_file",true)))Admin::get()->addError(upload_error($m));elseif(!preg_match('~~u',$m))Admin::get()->addError(lang(277));else{$P->updateParameter("exportFormat",$_POST["import_format"]);$Db=array_keys($l);preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$m,$z);$ya=count($z[0]);Driver::get()->begin();$Rj=($_POST["import_format"]=="csv;"?";":($_POST["import_format"]=="tsv"?"\t":","));$L=[];foreach($z[0]as$u=>$X){preg_match_all("~((?>\"[^\"]*\")+|[^$Rj]*)$Rj~",$X.$Rj,$pg);if(!$u&&!array_diff($pg[1],$Db)){$Db=$pg[1];$ya--;}else{$O=[];foreach($pg[1]as$q=>$yb)$O[idf_escape($Db[$q])]=($yb==""&&$l[$Db[$q]]["null"]?"NULL":q(preg_match('~^".*"$~s',$yb)?str_replace('""','"',substr($yb,1,-1)):$yb));$L[]=$O;}}$Bk=!$L||Driver::get()->insertUpdate($a,$L,$Fi);if($Bk)Driver::get()->commit();queries_redirect(remove_from_uri("page"),lang(278,$ya),$Bk);Driver::get()->rollback();}}$Rk=Admin::get()->getTableName($R);if(is_ajax()){page_headers();ob_start();}else
page_header(lang(55).": $Rk",[$Rk]);$O=null;if(isset($qj["insert"])||!support("table")){$Zh=[];foreach((array)$_GET["where"]as$X){if(isset($ae[$X["col"]])&&count($ae[$X["col"]])==1&&($X["op"]=="="||(!$X["op"]&&(is_array($X["val"])||!preg_match('~[_%]~',$X["val"])))))$Zh["set"."[".bracket_escape($X["col"])."]"]=$X["val"];}$O=$Zh?"&".http_build_query($Zh):"";}Admin::get()->printTableMenu($R,$O);if(!$c&&support("table"))echo"<p class='error'>".lang(279).($l?".":": ".error())."\n";else{echo"<form id='form' action=''>\n","<div style='display: none;'>";hidden_fields_get();if(DB!=""){echo
input_hidden("db",DB);if(isset($_GET["ns"]))echo
input_hidden("ns",$_GET["ns"]);}echo
input_hidden("select",$a),'<input type="submit" class="button" value="'.h(lang(55)).'">',"</div>\n","<div class='field-sets'>\n";Admin::get()->printSelectionColumns($M,$c);Admin::get()->printSelectionSearch($Z,$Ij,$t);Admin::get()->printSelectionOrder($D,$Hh,$t);Admin::get()->printSelectionLimit($w);Admin::get()->printSelectionLength($jl);Admin::get()->printSelectionAction($t);echo"</div>\n</form>\n";$E=isset($_GET["page"])?$_GET["page"]:null;if($E=="last"){$ge=Connection::get()->getValue(count_rows($a,$Z,$qf,$te));$E=(int)floor(max(0,intval($ge)-1)/$w);}else{$ge=false;$E=(int)$E;}$Jj=$M;$ue=$te;if(!$Jj){$Jj[]="*";$Wb=convert_fields($c,$l,$M);if($Wb)$Jj[]=substr($Wb,2);}foreach($M
as$u=>$X){$k=$l[idf_unescape($X)];if($k&&($Ja=convert_field($k)))$Jj[$u]="$Ja AS $X";}if(!$qf&&$Pl){foreach($Pl
as$u=>$X){$Jj[]=idf_escape($u);if($ue)$ue[]=idf_escape($u);}}$I=Driver::get()->select($a,$Jj,$Z,$ue,$D,$w,$E,true);if(!$I)echo"<p class='error'>".error()."\n";else{if(DIALECT=="mssql"&&$E)$I->seek($w*$E);echo"<form id='selection_form' action='' method='post' enctype='multipart/form-data'>\n","<div class='table-footer-parent'>\n";$L=[];while($K=$I->fetchAssoc()){if($E&&DIALECT=="oracle")unset($K["RNUM"]);$L[]=$K;}if($_GET["page"]!="last"&&$w&&$te&&$qf&&DIALECT=="sql")$ge=Connection::get()->getValue(" SELECT FOUND_ROWS()");$bd=false;if(!$L)echo"<p class='message'>".lang(88)."\n";else{$Ta=Admin::get()->getBackwardKeys($a,$Rk);echo"<div class='scrollable'>\n","<table id='table' class='nowrap checkable'>\n","<thead><tr>";if($te||!$M){echo"<th class='actions'><input type='checkbox' id='all-page' class='jsonly'>".script("gid('all-page').onclick = partial(formCheck, /check/);","");if(Admin::get()->isDataEditAllowed())echo" <a href='",h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."' title='",lang(280),"'>",icon_solo("edit-all"),"</a>";}$Xg=[];$le=[];reset($M);$Vi=1;foreach($L[0]as$u=>$X){if(!isset($Pl[$u])){$Lj=key($M);$X=isset($_GET["columns"][$Lj])?$_GET["columns"][$Lj]:[];$k=$l[$M?($X?$X["col"]:current($M)):$u];$A=($k?Admin::get()->getFieldName($k,$Vi):(isset($X["fun"])?"*":h($u)));if($A!=""){$Vi++;$Xg[$u]=$A;$b=idf_escape($u);$Ne=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($u);$yc="&desc%5B0%5D=1";echo"<th id='th[".h(bracket_escape($u))."]'>".script("mixin(qsl('th'), {onmouseover: partial(columnMouse), onmouseout: partial(columnMouse, ' hidden')});","");$ke=apply_sql_function(isset($X["fun"])?$X["fun"]:null,$A);$kk=isset($k["privileges"]["order"])||(isset($X["fun"])?$X["fun"]:null);if($kk)echo'<a href="',h($Ne.($D[0]==$b||$D[0]==$u?$yc:'')),'">',"$ke</a>";else
echo$ke;echo"<span class='column hidden'>";if($kk)echo"<a href='".h($Ne.$yc)."' title='".lang(62)."' class='button light'>",icon_solo("arrow-down"),"</a>";if(!isset($X["fun"])&&isset($k["privileges"]["where"]))echo'<a href="#fieldset-search" title="'.lang(59).'" class="button light jsonly">',icon_solo("search"),'</a>',script("qsl('a').onclick = partial(selectSearch, '".js_escape($u)."');");echo"</span>";}$le[$u]=isset($X["fun"])?$X["fun"]:null;next($M);}}$Xf=[];if($_GET["modify"]){foreach($L
as$K){foreach($K
as$u=>$X)$Xf[$u]=max($Xf[$u],min(40,strlen(utf8_decode($X))));}}if($Ta)echo"<th>".lang(17)."</th>";echo"</thead>\n","<tbody>\n";if(is_ajax())ob_end_clean();foreach(Admin::get()->fillForeignDescriptions($L,$ae)as$Ug=>$K){$Ll=unique_array($L[$Ug],$t);if(!$Ll){$Ll=[];reset($M);foreach($L[$Ug]as$u=>$X){if(!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~',current($M)))$Ll[$u]=$X;next($M);}}$Ml="";foreach($Ll
as$u=>$X){$k=isset($l[$u])?$l[$u]:null;if((DIALECT=="sql"||DIALECT=="pgsql")&&$k&&preg_match('~char|text|enum|set~',$k["type"])&&strlen($X)>64){$u=(strpos($u,'(')?$u:idf_escape($u));$u="MD5(".(DIALECT!='sql'||preg_match("~^utf8~",isset($k["collation"])?$k["collation"]:"")?$u:"CONVERT($u USING ".charset(Connection::get()).")").")";$X=md5($X);}$Ml
.="&".($X!==null?urlencode("where[".bracket_escape($u)."]")."=".urlencode($X===false?"f":$X):"null%5B%5D=".urlencode($u));}echo"<tr>";if($te||!$M){echo"<td class='actions'>",checkbox("check[]",substr($Ml,1),in_array(substr($Ml,1),(array)$_POST["check"]));if(!$qf&&Admin::get()->isDataEditAllowed())echo" <a href='",h(ME."edit=".urlencode($a).$Ml),"' class='edit' title='",lang(38),"'>",icon_solo("edit"),"</a>";}reset($M);foreach($K
as$u=>$X){if(isset($Xg[$u])){$b=current($M);$k=isset($l[$u])?$l[$u]:null;$x="";if($k&&is_blob($k)&&$X!="")$x=ME.'download='.urlencode($a).'&field='.urlencode($u).$Ml;if(!$x&&$X!==null){foreach((array)$ae[$u]as$o){if(count($ae[$u])==1||end($o["source"])==$u){$x="";foreach($o["source"]as$q=>$lk)$x
.=where_link($q,$o["target"][$q],$L[$Ug][$lk]);$x=($o["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.urlencode($o["db"]),ME):ME).'select='.urlencode($o["table"]).$x;if($o["ns"])$x=preg_replace('~([?&]ns=)[^&]+~','\1'.urlencode($o["ns"]),$x);if(count($o["source"])==1)break;}}}if($b=="COUNT(*)"){$x=ME."select=".urlencode($a);$q=0;foreach((array)$_GET["where"]as$W){if(!array_key_exists($W["col"],$Ll))$x
.=where_link($q++,$W["col"],$W["val"],$W["op"]);}foreach($Ll
as$zf=>$W)$x
.=where_link($q++,$zf,$W);}$jh=$X===null;$Oe=select_value($X,$x,$k,$jl);$od=bracket_escape($u);$r=h("val[$Ml][$od]");$Ai=isset($_POST["val"][$Ml][$od])?$_POST["val"][$Ml][$od]:null;$Rl=isset($k["privileges"]["update"])?$k["privileges"]["update"]:false;$Zc=!is_array($K[$u])&&is_utf8($Oe)&&$L[$Ug][$u]==$K[$u]&&!$le[$u]&&!(isset($k["generated"])?$k["generated"]:false);$U=($b&&preg_match('~^(AVG|MIN|MAX)\((.+)\)~',$b,$z)?$l[idf_unescape($z[2])]["type"]:(isset($k["type"])?$k["type"]:null));$Og=$U=="money"||($b&&preg_match('~^SUM\((.+)\)~',$b,$z)&&$l[idf_unescape($z[1])]["type"])=="money";$hl=$U&&preg_match('~text|json|lob~',$U);$lh=($U&&preg_match(number_type(),$U))||($b&&preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|UNIX_TIMESTAMP|TIME_TO_SEC|COUNT|SUM)\(~',$b));$wb=$lh&&($jh||is_numeric(strip_tags($Oe))||$Og)?"class='number'":"";echo"<td id='$r' $wb";if(($_GET["modify"]&&$Zc&&!$jh)||$Ai!==null){$bd=true;$ze=h($Ai!==null?$Ai:$K[$u]);echo" data-editing='true'>".($hl?"<textarea name='$r' cols='30' rows='".(substr_count($K[$u],"\n")+1)."'>$ze</textarea>":"<input class='input' name='$r' value='$ze' size='$Xf[$u]'>");}else{$mg=strpos($Oe,"<i>…</i>");if($Rl)echo" data-text='".($mg?2:($hl?1:0))."'".($Zc?"":" data-warning='".h(lang(281))."'");echo">$Oe";}}next($M);}if($Ta){echo"<td>";Admin::get()->printBackwardKeys($Ta,$L[$Ug]);echo"</td>";}echo"</tr>\n";}if(is_ajax())exit;echo"</tbody>\n",script("mixin(qs('#table tbody'), {onclick: partialArg(tableClick, false, ".(Admin::get()->isDataEditAllowed()?"true":"false")."), ondblclick: partialArg(tableClick, true), onkeydown: onEditingKeydown});"),"</table>\n",script("initToggles(gid('table'));"),"</div>\n";}if(!is_ajax()){if($L||$E){$qd=true;if($_GET["page"]!="last"){if(!$w||(count($L)<$w&&($L||!$E)))$ge=($E?$E*$w:0)+count($L);elseif(DIALECT!="sql"||!$qf){$ge=($qf?false:found_rows($R,$Z));if($ge<max(1e4,2*($E+1)*$w))$ge=first(slow_query(count_rows($a,$Z,$qf,$te)));elseif(DIALECT=='sql'||DIALECT=='pgsql')$qd=false;}}$Xh=($w!==null&&($ge===false||$ge>$w||$E));if($Xh){if(($ge===false?count($L)+1:$ge-$E*$w)>$w)echo'<p class="links">','<a href="',h(remove_from_uri("page")."&page=".($E+1)),'" class="loadmore">',icon("expand"),lang(282),'</a>',script("qsl('a').onclick = partial(loadNextPage, $w, '".lang(283)."…');","");echo"\n";}echo"<div class='table-footer'><div class='field-sets'>\n";if($Xh){$tg=($ge===false?$E+(count($L)>=$w?2:1):(int)floor(($ge-1)/$w));$Mc="<li>…</li>";echo"<fieldset>";if(DIALECT!="simpledb"){echo"<legend><a href='".h(remove_from_uri("page"))."'>".lang(284)."</a></legend>",script("qsl('a').onclick = function () { pageClick(this.href, +prompt('".lang(284)."', '".($E+1)."')); return false; };"),"<div id='fieldset-pagination' class='fieldset-content'><ul class='pagination'>",pagination(0,$E);if($E>5)echo$Mc;for($q=max(1,$E-4);$q<min($tg,$E+5);$q++)echo
pagination($q,$E);if($tg>0){if($E+5<$tg)echo$Mc;echo($qd&&$ge!==false?pagination($tg,$E):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$tg'>".lang(285)."</a>");}echo"</ul></div>";}else{echo"<legend>".lang(284)."</legend>","<div id='fieldset-pagination'><ul class='pagination'>",pagination(0,$E);if($E>1)echo$Mc;if($E)echo
pagination($E,$E);if($tg>$E){echo
pagination($E+1,$E);if($tg>$E+1)echo$Mc;}echo"</ul></div>";}echo"</fieldset>\n";}echo"<fieldset>","<legend>".lang(286)."</legend><div class='fieldset-content'>";$Fc=($qd?"":"~ ").$ge;echo
checkbox("all",1,0,($ge!==false?($qd?"":"~ ").lang(186,$ge):""),"const checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$Fc' : checked); selectCount('selected2', this.checked || !checked ? '$Fc' : checked);")."\n","</div></fieldset>\n";if(Admin::get()->isDataEditAllowed()){echo"<fieldset",($_GET["modify"]?'':' class="jsonly"'),">","<legend>",lang(280),"</legend>";$yj=($_GET["modify"]?"":" data-inline-edit='1'".($bd?"":" disabled"));echo"<div class='fieldset-content'",($_GET["modify"]?"":" title='".lang(276)."'"),">","<input type='submit' class='button' id='modify-save' value='",lang(112),"'",$yj,">","</div>","</fieldset>\n","<fieldset>","<legend>",lang(158)," <span id='selected'></span></legend>","<div class='fieldset-content'>","<input type='submit' class='button' name='edit' value='",lang(38),"'> ","<input type='submit' class='button' name='clone' value='",lang(272),"'> ","<input type='submit' class='button' name='delete' value='",lang(116),"'>",confirm(),"</div>","</fieldset>\n";}$ce=Admin::get()->getDumpFormats();foreach((array)$_GET["columns"]as$b){if($b["fun"]){unset($ce['sql']);break;}}if($ce){print_fieldset_start("export",lang(74)." <span id='selected2'></span>","export");echo
html_select("format",$ce,$P->getParameter("exportFormat"));$Uh=Admin::get()->getDumpOutputs();echo($Uh?" ".html_select("output",$Uh,$P->getParameter("exportOutput")):"")," <input type='submit' class='button' name='export' value='".lang(74)."'>\n";print_fieldset_end("export");}echo"</div></div>\n",script("initTableFooter()");}echo"</div>\n";if(Admin::get()->isDataEditAllowed()){echo"<p>","<a href='#import'>",icon("import"),lang(73),"</a>",script("qsl('a').onclick = partial(toggle, 'import');",""),"</p>","<p id='import'",($_POST["import"]?"":" class='hidden'"),">";if(ini_bool("file_uploads"))echo"<input type='file' name='csv_file'> ",html_select("import_format",["csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"],$P->getParameter("exportFormat"))," <input type='submit' class='button default' name='import' value='".lang(73)."'>",file_upload_form_script("selection_form","csv_file");else
echo
lang(193);echo"</p>";}echo
input_token(),"</form>\n",(!$te&&$M?"":script("tableCheck();"));}else
echo"</div>\n";}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$uk=isset($_GET["status"]);$T=$uk?lang(151):lang(150);page_header($T,[$T]);$gm=($uk?Admin::get()->getStatusVariables():Admin::get()->getServerVariables());if(!$gm)echo"<p class='message'>",lang(88),"</p>\n";else{echo"<div class='scrollable'><table>\n";foreach($gm
as$K){echo"<tr>";$u=array_shift($K);echo"<th><code class='jush-".DIALECT.($uk?"status":"set")."'>".h($u)."</code></th>";foreach($K
as$X)echo"<td>",nl2br(h($X)),"</td>";echo"</tr>\n";}echo"</table></div>\n";}}elseif(isset($_GET["script"])){header("Content-Type: text/javascript; charset=utf-8");if($_GET["script"]=="db"){$Ek=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];$f=[];$mc=null;foreach(table_status()as$A=>$R){$f["Comment-$A"]=h($R["Comment"]);if(!is_view($R)||preg_match('~materialized~i',$R["Engine"])){$f["Engine-$A"]=h($R["Engine"]);$_b=isset($R["Collation"])?$R["Collation"]:"";if($_b==""){if($mc===null)$mc=db_collation(DB,collations())??"";$_b=$mc;}$f["Collation-$A"]=h($_b);foreach($Ek+["Auto_increment"=>0,"Rows"=>0]as$u=>$X){if($R[$u]!=""){$X=format_number($R[$u]);if($X>=0)$f["$u-$A"]=($u=="Rows"&&$X&&$R["Engine"]==(DIALECT=="pgsql"?"table":"InnoDB")?"~ $X":$X);if(isset($Ek[$u]))$Ek[$u]+=($R["Engine"]!="InnoDB"||$u!="Data_free"?$R[$u]:0);}elseif(array_key_exists($u,$R))$f["$u-$A"]="?";}}}foreach($Ek
as$u=>$X)$f["sum-$u"]=format_number($X);echo
json_encode($f,JSON_UNESCAPED_UNICODE);}elseif($_GET["script"]=="kill")Connection::get()->query("KILL ".number($_POST["kill"]));else{$f=[];foreach(count_tables(Admin::get()->getDatabases())as$h=>$X){$f["tables-$h"]=$X;$f["size-$h"]=db_size($h);}echo
json_encode($f,JSON_UNESCAPED_UNICODE);}exit;}else{$al=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($al&&!$_POST["search"]){$I=true;$_="";if(DIALECT=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$I=truncate_tables($_POST["tables"]);$_=lang(287);}elseif($_POST["move"]){$I=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$_=lang(288);}elseif($_POST["copy"]){$I=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$_=lang(289);}elseif($_POST["drop"]){if($_POST["views"])$I=drop_views($_POST["views"]);if($I&&$_POST["tables"])$I=drop_tables($_POST["tables"]);$_=lang(290);}elseif(DIALECT=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$Q){foreach(get_rows("PRAGMA integrity_check(".q($Q).")")as$K)$_
.="<b>".h($Q)."</b>: ".h($K["integrity_check"])."<br>";}}elseif(DIALECT!="sql"){$I=(DIALECT=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?" ANALYZE":""),$_POST["tables"]));$_=lang(291);}elseif(!$_POST["tables"])$_=lang(78);elseif($I=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('AdminNeo\idf_escape',$_POST["tables"])))){while($K=$I->fetchAssoc())$_
.="<b>".h($K["Table"])."</b>: ".h($K["Msg_text"])."<br>";}queries_redirect($_SERVER["REQUEST_URI"],$_,(bool)$I);}if($_GET["ns"]=="")page_header(lang(30).": ".h(DB),true);else
page_header(lang(181).": ".h($_GET["ns"]),true);Admin::get()->printDatabaseMenu();if($_GET["ns"]===""){echo"<h2 id='schemas'>".lang(292)."</h2>\n";$Ej=Admin::get()->getSchemas();if(!$Ej)echo"<p class='message'>".lang(293)."\n";else{echo"<div class='scrollable'>\n","<table class='nowrap'>\n",'<thead><tr class="wrap"><th>',lang(181),"</th></tr></thead>";foreach($Ej
as$A)echo"<tr><th><a href='",h(ME),"ns=".urlencode($A),"' title='",lang(294),"'>".h($A)."</a></th></tr>";echo'</table></div>';}echo'<p class="links"><a href="'.h(ME).'scheme=">'.icon("database-add").lang(76)."</a>\n";}else{echo"<h2 id='tables-views'>".lang(295)."</h2>\n";$Vk=['sql'=>'show-table-status.html','mariadb'=>'reference/sql-statements/administrative-sql-statements/show/show-table-status'];$mc=db_collation(DB,collations());$c=["Engine"=>["label"=>lang(162),"doc"=>doc_link(['sql'=>'storage-engines.html','mariadb'=>'server-usage/storage-engines']),],];if($mc!="")$c["Collation"]=["label"=>lang(45),"doc"=>doc_link(['sql'=>'charset-charsets.html','mariadb'=>'reference/data-types/string-data-types/character-sets/supported-character-sets-and-collations']),];$c+=["Data_length"=>["label"=>lang(296),"doc"=>doc_link($Vk+['pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'REFRN20286']),"link"=>"create","title"=>lang(35),],"Index_length"=>["label"=>lang(297),"doc"=>doc_link($Vk+['pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT']),"link"=>"indexes","title"=>lang(166),],"Data_free"=>["label"=>lang(298),"doc"=>doc_link($Vk),"link"=>"edit","title"=>lang(7),],"Auto_increment"=>["label"=>lang(47),"doc"=>doc_link(['sql'=>'example-auto-increment.html','mariadb'=>'reference/data-types/auto_increment']),"link"=>"auto_increment=1&create","title"=>lang(35),],"Rows"=>["label"=>lang(299),"doc"=>doc_link($Vk+['pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'REFRN20286']),"link"=>"select","title"=>lang(33),],];if(support("comment"))$c["Comment"]=["label"=>lang(46),"doc"=>doc_link($Vk+['pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE']),];$D=(is_string($_GET["order"])?$_GET["order"]:"");$zc=null;if(preg_match('~^(.+)-(asc|desc)$~',$D,$y)){$D=$y[1];$zc=($y[2]=="desc");}if($D!="__table"&&!isset($c[$D]))$D="";if($zc===null)$zc=isset($c[$D]["link"]);$zm=($D!=""&&$D!="__table");$Yk=($zm?table_status():tables_list());if(!$Yk)echo"<p class='message'>".lang(78)."\n";else{echo"<form action='' method='post'>\n","<div class='table-footer-parent'>\n";if(support("table")){echo"<div class='field-sets'>\n","<fieldset><legend>".lang(300)." <span id='selected2'></span></legend><div class='fieldset-content'>",html_select("op",Admin::get()->getOperators(),isset($_POST["op"])?$_POST["op"]:Driver::get()->getLikeOperator()),"<input type='search' class='input' name='query' value='".h($_POST["query"])."'>",script("qsl('input').onkeydown = partialArg(bodyKeydown, 'search');","")," <input type='submit' class='button' name='search' value='".lang(59)."'>\n","</div></fieldset>\n","</div>\n";if($_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=$_POST["op"];search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable'>\n",'<thead><tr class="wrap">','<td class="actions"><input id="check-all" type="checkbox" class="input jsonly">'.script("gid('check-all').onclick = partial(formCheck, /^(tables|views)\[/);","");$Vg=($D==""||$D=="__table");$Qk=($Vg&&!$zc?ME."order=__table-desc":substr(ME,0,-1));echo'<th><a href="'.h($Qk).'">'.lang(8).'</a>';foreach($c
as$u=>$b){$Ac=($u===$D?!$zc:isset($b["link"]));echo'<td><a href="'.h(ME)."order=$u-".($Ac?"desc":"asc").'">'.$b["label"].'</a>'.$b["doc"];}echo"</thead>\n","<tbody>\n";if($D=="__table"){if($zc)$Yk=array_reverse($Yk,true);}elseif($D){uasort($Yk,function($ra,$Qa)use($D,$zc){$_m=isset($ra[$D])?$ra[$D]:null;$Am=isset($Qa[$D])?$Qa[$D]:null;$I=($_m<$Am?-1:($_m>$Am?1:0));return($zc?-$I:$I);});}$Ek=["Data_length"=>0,"Index_length"=>0,"Data_free"=>0];$S=0;foreach($Yk
as$A=>$uk){$km=($zm?is_view($uk):$uk!==null&&!preg_match('~table|sequence~i',$uk));$hd=($zm?(isset($uk["Engine"])?$uk["Engine"]:""):$uk);$r=h("Table-".$A);echo'<tr><td class="actions">'.checkbox(($km?"views[]":"tables[]"),$A,in_array("$A",$al,true),"","","",$r);if(!Admin::get()->getSettings()->isSelectionPreferred()&&(support("table")||support("indexes")))$ta="table";else$ta="select";echo"<th><a href='",h(ME),"$ta=",urlencode($A),"' id='$r'>",h($A),"</a></th>";if($km&&!preg_match('~materialized~i',$hd)){$T=lang(161);$Eb=count($c)-(support("comment")?2:1);echo'<td colspan="'.$Eb.'">'.(support("view")?"<a href='".h(ME)."view=".urlencode($A)."' title='".lang(36)."'>$T</a>":$T),'<td align="right"><a href="'.h(ME)."select=".urlencode($A).'" title="'.lang(33).'">?</a>';}else{foreach($c
as$u=>$b){if($u=="Comment")continue;$r=" id='$u-".h($A)."'";$x=isset($b["link"])?$b["link"]:"";if(!$x){$X="";if($zm){$X=isset($uk[$u])?$uk[$u]:"";if($u=="Collation"&&$X=="")$X=$mc;}echo"<td$r>".h($X);continue;}$X="?";if($zm){$B=isset($uk[$u])?$uk[$u]:"";if(is_numeric($B)&&$B>=0){$X=($u=="Rows"&&$B&&$hd==(DIALECT=="pgsql"?"table":"InnoDB")?"~ ":"").format_number($B);if(isset($Ek[$u])&&($hd!="InnoDB"||$u!="Data_free"))$Ek[$u]+=$B;}}echo"<td align='right'>".(support("table")||$u=="Rows"||(support("indexes")&&$u!="Data_length")?"<a href='".h(ME."$x=").urlencode($A)."'$r title='".$b["title"]."'>$X</a>":"<span$r>$X</span>");}$S++;}echo(support("comment")?"<td id='Comment-".h($A)."'>".($zm?h(isset($uk["Comment"])?$uk["Comment"]:""):""):""),"\n";}echo"</tbody>\n",script("mixin(qsl('tbody'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),"<tfoot><tr>","<td><th>".lang(273,count($Yk)),"<td>".h(DIALECT=="sql"?Connection::get()->getValue("SELECT @@default_storage_engine"):""),($mc!=""?"<td>".h($mc):"");foreach($Ek
as$u=>$Dk)echo"<td align='right' id='sum-$u'>".($zm?format_number($Dk):"");echo"<td></td><td></td>";if(support("comment"))echo"<td></td>";echo"</tr></tfoot>\n","</table>\n","</div>\n",($zm?"":script("ajaxSetHtml('".js_escape(ME)."script=db');"));if(Admin::get()->isDataEditAllowed()){echo"<div class='table-footer'><div class='field-sets'>\n";$dm="<input type='submit' class='button' value='".lang(301)."'> ".help_script("VACUUM");$Dh="<input type='submit' class='button' name='optimize' value='".lang(302)."'> ".help_script(DIALECT=="sql"?"OPTIMIZE TABLE":"VACUUM ANALYZE");echo"<fieldset><legend>".lang(158)." <span id='selected'></span></legend><div class='fieldset-content'>".(DIALECT=="sqlite"?$dm."<input type='submit' class='button' name='check' value='".lang(303)."'> ".help_script("PRAGMA integrity_check"):(DIALECT=="pgsql"?$dm.$Dh:(DIALECT=="sql"?"<input type='submit' class='button' value='".lang(304)."'> ".help_script("ANALYZE TABLE").$Dh."<input type='submit' class='button' name='check' value='".lang(303)."'> ".help_script("CHECK TABLE")."<input type='submit' class='button' name='repair' value='".lang(305)."'> ".help_script("REPAIR TABLE"):"")))."<input type='submit' class='button' name='truncate' value='".lang(306)."'> ".help_script(DIALECT=="sqlite"?"DELETE":("TRUNCATE".(DIALECT=="pgsql"?"":" TABLE"))).confirm()."<input type='submit' class='button' name='drop' value='".lang(159)."'>".help_script("DROP TABLE").confirm()."\n";$g=(support("scheme")?Admin::get()->getSchemas():Admin::get()->getDatabases());echo"</div></fieldset>\n";$Gj="";if(count($g)!=1&&DIALECT!="sqlite"){echo"<fieldset><legend>".lang(307)." <span id='selected3'></span></legend><div>";$h=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($g?html_select("target",$g,$h,"","label-move"):'<input class="input" name="target" value="'.h($h).'" autocapitalize="off">')," <input type='submit' class='button' name='move' value='".lang(308)."'>",(support("copy")?" <input type='submit' class='button' name='copy' value='".lang(309)."'> ".checkbox("overwrite",1,$_POST["overwrite"],lang(310)):""),"</div></fieldset>\n";$Gj=" selectCount('selected3', formChecked(this, /^(tables|views)\[/));";}echo
input_hidden("all"),script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^(tables|views)\[/));".(support("table")?" selectCount('selected2', formChecked(this, /^tables\[/) || $S);":"")."$Gj }"),input_token(),"</div></div>\n",script("initTableFooter()");}echo"</div>\n","</form>\n",script("tableCheck();");}echo'<p class="links"><a href="',h(ME),'create=">',icon("table-add"),lang(77),"</a>\n";if(support("view"))echo'<a href="',h(ME),'view=">',icon("view-add"),lang(235),"</a>\n";if(support("routine")){echo"<h2 id='routines'>".lang(177)."</h2>\n";$uj=routines();if($uj){$Kb=$uj[0]["ROUTINE_COMMENT"]!==null;echo"<table>\n",'<thead><tr>','<th>',lang(216),'</th><td>',lang(44),'</td><td>',lang(252),"</td>";if($Kb)echo"<td>",lang(46),"</td>";echo"<td></td>","</tr></thead>\n";foreach($uj
as$K){$A=($K["SPECIFIC_NAME"]==$K["ROUTINE_NAME"]?"":"&name=".urlencode($K["ROUTINE_NAME"]));echo'<tr>','<th><a href="',h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').urlencode($K["SPECIFIC_NAME"]).$A),'">',h($K["ROUTINE_NAME"]),'</a></th>','<td>',h($K["ROUTINE_TYPE"]),'</td>','<td>',h($K["DTD_IDENTIFIER"]),'</td>';if($Kb)echo'<td>',truncate_utf8(preg_replace('~\s{2,}~'," ",trim($K["ROUTINE_COMMENT"])),50),'</td>';echo'<td><a href="'.h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').urlencode($K["SPECIFIC_NAME"]).$A).'">'.lang(169)."</a></td>";}echo"</table>\n";}echo'<p class="links">';if(support("procedure"))echo'<a href="',h(ME),'procedure=">',icon("function-add"),lang(251),"</a>";echo'<a href="',h(ME),'function=">',icon("function-add"),lang(250),"</a>\n","</p>\n";}if(support("event")){echo"<h2 id='events'>".lang(178)."</h2>\n";$L=get_rows("SHOW EVENTS");if($L){echo"<table>\n","<thead><tr><th>".lang(216)."<td>".lang(311)."<td>".lang(241)."<td>".lang(242)."<td></thead>\n";foreach($L
as$K)echo"<tr>","<th>".h($K["Name"]),"<td>".($K["Execute at"]?lang(312)."<td>".$K["Execute at"]:lang(243)." ".$K["Interval value"]." ".$K["Interval field"]."<td>$K[Starts]"),"<td>$K[Ends]",'<td><a href="'.h(ME).'event='.urlencode($K["Name"]).'">'.lang(169).'</a>';echo"</table>\n";$pd=Connection::get()->getValue("SELECT @@event_scheduler");if($pd&&$pd!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($pd)."\n";}echo'<p class="links"><a href="',h(ME),'event=">',icon("event-add"),lang(240),"</a></p>\n";}}}page_footer();