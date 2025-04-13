<?php namespace AdminNeo;

use function ProcessWire\wire;

class ProcessWirePlugin {

    private $gridSize2x;

    public function __construct() {

        // autologin based on https://steampixel.de/simply-auto-login-to-your-adminer/
        // a more complex version is available at: https://github.com/jeliebig/Adminer-Autologin/blob/master/login-env-vars.php
        if(!$_GET['username'] || !isset($_COOKIE['neo_permanent']) || $_COOKIE['neo_permanent'] == '') {
            $_POST['auth'] = [
                'driver' => 'mysql',
                'server' => wire('config')->dbHost . (wire('config')->dbPort ? ':' . wire('config')->dbPort : ''),
                'db' => wire('config')->dbName,
                'username' => wire('config')->dbUser,
                'password' => wire('config')->dbPass,
                'permanent' => 1
            ];
        }

        $defaultGridSize = 130;
		$options = wire('config')->adminThumbOptions;
		if(!is_array($options)) $options = array();
		$gridSize = empty($options['gridSize']) ? $defaultGridSize : (int) $options['gridSize'];
		if($gridSize < 100) $gridSize = $defaultGridSize; // establish min of 100
		if($gridSize >= ($defaultGridSize * 2)) $gridSize = $defaultGridSize; // establish max of 259
        $this->gridSize2x = $gridSize * 2;
    }

    // v4.x wrappers
    public function credentials() {
        return $this->getCredentials();
    }
    public function login($username, $password) {
        $this->authenticate($username, $password);
    }
    public function head() {
        $this->printToHead();
    }
    public function databases($flush = true) {
        return $this->getDatabases($flush);
    }
    public function selectVal(&$val, $link, $field, $original) {
        return $this->formatSelectionValue($val, $link, $field, $original);
    }
    public function messageQuery($query, $time, $failed = false) {
        $this->formatMessageQuery($query, $time, $failed);
    }
    public function sqlCommandQuery($query) {
        $this->formatSqlCommandQuery($query);
    }


    // modifier functions
    public function getCredentials(): array {
        return array(wire('config')->dbHost . (wire('config')->dbPort ? ':' . wire('config')->dbPort : ''), wire('config')->dbUser, wire('config')->dbPass);
    }

    public function authenticate(string $username, string $password) {
        return true;
    }

    public function printToHead(): void {
    ?>
        <style>
            .download-btn {
                display: inline-block;
                border: 1px solid var(--button-border);
                border-radius: var(--input-border-radius);
                background: var(--button-bg);
                color: var(--button-text);
                padding: 10px 15px;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
                text-align: center;
            }

            .image-modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.8);
                justify-content: center;
                align-items: center;
                padding: 20px;
            }

            .image-container {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .image-modal img {
                max-width: 90%;
                max-height: 80%;
            }

            .image-modal iframe {
                display: none;
                width: 90vw;
                height: 80vh;
                border: none;
                background-color: #FFFFFF;
            }

            .image-modal video,
            .image-modal audio {
                display: none;
                max-width: 90%;
                max-height: 80%;
            }

            .image-modal-filename {
                color: white;
                font-size: 16px;
                margin-top: 10px;
                text-align: center;
            }

            /* Navigation & Close Button */
            .image-modal-close {
                position: absolute;
                top: 10px;
                right: 20px;
                color: white;
                cursor: pointer;
                background: rgba(0, 0, 0, 0.5);
                padding: 0 8px 3px 8px;
                border-radius: 5px;
                user-select: none;
                font-size: 24px;
            }

            .image-modal-nav {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                font-size: 30px;
                color: white;
                cursor: pointer;
                background: rgba(0, 0, 0, 0.5);
                padding: 3px 8px;
                border-radius: 5px;
                user-select: none;
            }

            .image-modal-prev {
                left: 20px;
            }

            .image-modal-next {
                right: 20px;
            }

            td {
                position: relative;
            }

            td .image-thumb {
                display: inline-block;
                padding-right: 30px;
            }

            td .file-icon {
                position: absolute;
                right: 5px;
                top: 50%;
                transform: translateY(-50%);
            }

            td .image-thumb-icon {
                position: absolute;
                right: 5px;
                top: 50%;
                transform: translateY(-50%);
                height: 18px;
                width: auto;
            }

        </style>

        <div id="image-modal" class="image-modal">
            <span class="image-modal-close">&times;</span>
            <span class="image-modal-nav image-modal-prev">&#10094;</span>
            <div class="image-container">
                <img id="modal-image" class="modal-file" src="" alt="">
                <iframe id="modal-iframe" class="modal-file" style="display:none;" frameborder="0"></iframe>
                <video id="modal-video" class="modal-file" style="display:none;" controls></video>
                <audio id="modal-audio" class="modal-file" style="display:none;" controls></audio>
                <a id="modal-download-btn" href="#" class="download-btn" style="display:none;">Download File</a>
                <div id="modal-filename" class="image-modal-filename"></div>
            </div>
            <span class="image-modal-nav image-modal-next">&#10095;</span>
        </div>


        <script>
            window.HttpAdminUrl = '<?=wire('config')->urls->httpAdmin?>';

            document.addEventListener("DOMContentLoaded", function () {
                let items = Array.from(document.querySelectorAll(".image-thumb"));
                let currentIndex = -1;
                let modal = document.getElementById("image-modal");
                let modalImg = document.getElementById("modal-image");
                let modalIframe = document.getElementById("modal-iframe");
                let modalVideo = document.getElementById("modal-video");
                let modalAudio = document.getElementById("modal-audio");
                let modalFilename = document.getElementById("modal-filename");
                let modalDownloadBtn = document.getElementById("modal-download-btn");
                let prevBtn = document.querySelector(".image-modal-prev");
                let nextBtn = document.querySelector(".image-modal-next");

                function showFile(index) {
                    if (index >= 0 && index < items.length) {

                        modalVideo.pause();
                        modalAudio.pause();

                        currentIndex = index;
                        let fileType = items[currentIndex].getAttribute("data-type");
                        let fileSrc = items[currentIndex].getAttribute("data-src");
                        let fileName = items[currentIndex].getAttribute("data-filename");

                        modalFilename.innerHTML = '<a target="_blank" style="color:#FFFFFF; text-decoration: underline" href="'+fileName+'">'+fileName+'</a>';

                        // Hide all media elements first
                        modalImg.style.display = "none";
                        modalIframe.style.display = "none";
                        modalDownloadBtn.style.display = "none";
                        modalVideo.style.display = "none";
                        modalAudio.style.display = "none";

                        if (fileType === "image") {
                            modalImg.style.display = "block";
                            modalImg.setAttribute("src", fileSrc);
                        }
                        else if (fileType === "iframe") {
                            modalIframe.style.display = "block";
                            modalIframe.setAttribute("src", fileSrc);
                        }
                        else if (fileType === "video") {
                            modalVideo.style.display = "block";
                            modalVideo.setAttribute("src", fileSrc);
                        }
                        else if (fileType === "audio") {
                            modalAudio.style.display = "block";
                            modalAudio.setAttribute("src", fileSrc);
                        }
                        else if (fileType === "download") {
                            modalIframe.style.display = "none"; // Hide iframe since we're not embedding
                            modalDownloadBtn.style.display = "block"; // Show download button
                            modalDownloadBtn.setAttribute("href", fileName);
                            modalDownloadBtn.setAttribute("download", ""); // Enable direct download
                        }

                        modal.style.display = "flex";
                        prevBtn.style.display = currentIndex === 0 ? "none" : "block";
                        nextBtn.style.display = currentIndex === items.length - 1 ? "none" : "block";
                    }
                }

                items.forEach((thumb, index) => {
                    thumb.addEventListener("click", function (event) {
                        if (event.ctrlKey || event.metaKey) {
                            return;
                        }
                        event.stopPropagation();
                        event.preventDefault();
                        currentIndex = index;
                        showFile(currentIndex);
                        return false;
                    }, true);
                });

                modal.addEventListener("click", function (event) {
                    if (event.target.classList.contains("image-modal-close") || event.target.id === "image-modal") {
                        modal.style.display = "none";
                        // Stop any media that might be playing
                        modalVideo.pause();
                        modalAudio.pause();
                        // Clear iframe src to stop any potential loading
                        modalIframe.setAttribute("src", "");
                    }
                    else if (event.target.classList.contains("image-modal-prev")) {
                        showFile(currentIndex - 1);
                    }
                    else if (event.target.classList.contains("image-modal-next")) {
                        showFile(currentIndex + 1);
                    }
                    event.stopPropagation();
                });

                document.body.addEventListener("keydown", function (event) {
                    if (modal.style.display === "flex") {
                        if (event.keyCode === 37 || event.keyCode === 38) { // Left or Up arrow
                            event.preventDefault();
                            showFile(currentIndex - 1);
                        }
                        else if (event.keyCode === 39 || event.keyCode === 40) { // Right or Down arrow
                            event.preventDefault();
                            showFile(currentIndex + 1);
                        }
                        else if (event.keyCode === 27) { // Escape key
                            event.preventDefault();
                            modal.style.display = "none";
                            // Stop any media that might be playing
                            modalVideo.pause();
                            modalAudio.pause();
                            // Clear iframe src to stop any potential loading
                            modalIframe.setAttribute("src", "");
                        }
                    }
                });
            });
        </script>
    <?php
    }


    public function getDatabases($flush = true): array {
        return [wire('config')->dbName];
    }

    public function formatSelectionValue(?string $val, ?string $link, ?array $field, ?string $original): ?string {

        // check if the current field is the pages_id column and store for use in other columns on the same row (ie to get image paths)
        static $pages_id = null;
        if ($field['field'] == 'pages_id') {
            $pages_id = $val;
        }

        $label = array('title', 'name', 'status');
        if(wire('modules')->isInstalled('PagePaths')) {
            $label[] = 'url';
        }

        if(!$field || !isset($_GET['select']) || in_array($_GET['select'], array('fieldgroups', 'caches'))) {
            return null;
        }
        elseif ($val === null) {
			$val = "<i>NULL</i>";
		}
        elseif($_GET['select'] == 'modules' && $field['field'] == 'class') {
            $val = '<a href="'.wire('config')->urls->admin.'module/edit/?name='.$val.'" target="_parent">'.$val.'</a>';
        }
        elseif(ctype_digit("$original") || ctype_digit(str_replace(array(',', 'pid'), '', "$original"))) {

            $valid_page_fields = array('pid', 'pages_id', 'parent_id', 'parents_id', 'source_id', 'language_id', 'data');

            if($_GET['select'] == 'hanna_code' && $field['field'] == 'id') {
                $val = '<a href="'.wire('config')->urls->admin.'setup/hanna-code/edit/?id='.$val.'" target="_parent">'.$val.'</a>';
            }
            elseif($_GET['select'] == 'templates' && $field['field'] == 'id') {
                $val = '<a href="'.wire('config')->urls->admin.'setup/template/edit/?id='.$val.'" target="_parent">'.$val.'</a>';
            }
            elseif($_GET['select'] != 'templates' && $field['field'] == 'templates_id') {
                $name = wire('templates')->get('id='.$val)->get('label|name');
                if($name) {
                    $val = '<a href="'.wire('config')->urls->admin.'setup/template/edit/?id='.$val.'" target="_parent" title="'.$name.'">'.$val.'</a>';
                }
            }
            elseif($_GET['select'] == 'fields' && $field['field'] == 'id') {
                $val = '<a href="'.wire('config')->urls->admin.'setup/field/edit/?id='.$val.'" target="_parent">'.$val.'</a>';
            }
            elseif(in_array($field['field'], array('field_id', 'fields_id'))) {
                $f = wire('fields')->get('id='.$val);
                if($f) {
                    $name = $f->get('label|name');
                    $val = '<a href="'.wire('config')->urls->admin.'setup/field/edit/?id='.$val.'" target="_parent" title="'.$name.'">'.$val.'</a>';
                }
            }
            elseif($_GET['select'] == 'pages' && $field['field'] == 'id') {
                if(method_exists(wire('pages'), 'getRaw')) {
                    $name = wire('pages')->getRaw('id='.$val, $label);
                    if($name) {
                        $val_with_status = $this->formatPageStatus($val, $name['status']);
                        $name = (isset($name['title']) ? $name['title'] : $name['name']) . (isset($name['url']) ? ' ('.$name['url'].')' : '');
                        $val = '<a href="'.wire('config')->urls->admin.'page/edit/?id='.$val.'" target="_parent" title="'.$name.'">'.$val_with_status.'</a>';
                    }
                }
                else {
                    $val = '<a href="'.wire('config')->urls->admin.'page/edit/?id='.$val.'" target="_parent">'.$val.'</a>';
                }
            }
            elseif(in_array($field['field'], array('uid', 'user_id', 'created_users_id', 'modified_users_id', 'user_created', 'user_updated'))) {
                if(method_exists(wire('pages'), 'getRaw')) {
                    $name = wire('pages')->getRaw('id='.$val, $label);
                    if($name) {
                        $val_with_status = $this->formatPageStatus($val, $name['status']);
                        $name = (isset($name['title']) ? $name['title'] : $name['name']) . (isset($name['url']) ? ' ('.$name['url'].')' : '');
                        $val = '<a href="'.wire('config')->urls->admin.'access/users/edit/?id='.$val.'" target="_parent" title="'.$name.'">'.$val_with_status.'</a>';
                    }
                }
                else {
                    $val = '<a href="'.wire('config')->urls->admin.'access/users/edit/?id='.$val.'" target="_parent">'.$val.'</a>';
                }
            }
            elseif(strpos($_GET['select'], 'field_') !== false || in_array($field['field'], $valid_page_fields)) {

                $f = wire('fields')->get(str_replace('field_', '', $_GET['select']));
                if($f && $f->type instanceof \ProcessWire\FieldtypeTable && strpos($f->type->getColumn($f, $field['field'])['type'], 'page') !== false) {
                    $valid_page_fields[] = $field['field'];
                }
                elseif($f && $f->type instanceof \ProcessWire\FieldtypeCombo && $f->getComboSettings()->getSubfieldType($field['field']) === 'Page') {
                    $valid_page_fields[] = $field['field'];
                }

                if($_GET['select'] == 'field_process' && $field['field'] == 'data') {
                    $name = wire('modules')->getModuleClass($val);
                    if($name) {
                        $val = '<a href="'.wire('config')->urls->admin.'module/edit/?name='.$name.'" target="_parent" title="'.$name.'">'.$val.'</a>';
                    }
                }
                elseif(in_array($field['field'], $valid_page_fields)) {
                    $data_is_page = false;
                    if($field['field'] == 'data') {
                        if(isset($f) && ($f->type instanceof \ProcessWire\FieldtypePage || $f->type instanceof \ProcessWire\FieldtypePageIDs || $f->type instanceof \ProcessWire\FieldtypeRepeater)) {
                            $data_is_page = true;
                        }
                    }
                    if($field['field'] != 'data' || ($field['field'] == 'data' && $data_is_page)) {
                        $allids = [];
                        foreach(explode(',', $val) as $v) {
                            if(method_exists(wire('pages'), 'getRaw')) {
                                $name = wire('pages')->getRaw('id='.str_replace('pid', '', $v), $label);
                                if($name) {
                                    $v_with_status = $this->formatPageStatus($v, $name['status']);
                                    $name = (isset($name['title']) ? $name['title'] : $name['name']) . (isset($name['url']) ? ' ('.$name['url'].')' : '');
                                    $allids[] = '<a href="'.wire('config')->urls->admin.'page/edit/?id='.str_replace('pid', '', $v).'" target="_parent" title="'.$name.'">'.$v_with_status.'</a>';
                                }
                            }
                            else {
                                $allids[] = '<a href="'.wire('config')->urls->admin.'page/edit/?id='.$v.'" target="_parent">'.$v.'</a>';
                            }
                        }
                        $val = implode(',', $allids);
                    }
                }

            }
        }
        elseif(preg_match('/\.(jpg|jpeg|png|gif|svg|webp|bmp)$/i', $val)) {
            $thumb = $this->getAvailableThumb($val, $pages_id);
            $fullPath = $this->getFullPath($val, $pages_id);
            $val = '<a title="Modal viewer" href="' . $fullPath . '" data-src="' . $fullPath . '" data-type="image" class="image-thumb" data-filename="' . wire('config')->urls->httpRoot.ltrim(htmlspecialchars($fullPath), '/') . '">'
                 . htmlspecialchars($val) . '</a><a title="Download" href="' . $fullPath . '" download>'.$thumb.'</a>';
        }
        elseif(preg_match('/\.(mp4|webm|ogg|ogv|mov|avi|wmv|mkv|flv)$/i', $val)) {
            $fullPath = $this->getFullPath($val, $pages_id);
            $extension = pathinfo($val, PATHINFO_EXTENSION);
            list($icon, $iconClass) = $this->getFileTypeIcon($extension);
            $val = '<a title="Modal viewer" href="' . $fullPath . '" data-src="' . $fullPath . '" data-type="video" class="image-thumb" data-filename="' . wire('config')->urls->httpRoot.ltrim(htmlspecialchars($fullPath), '/') . '">'
                 . htmlspecialchars($val) . '</a><a title="Download" href="' . $fullPath . '" download><span class="file-icon ' . $iconClass . '">' . $icon . '</span></a>';
        }
        elseif(preg_match('/\.(mp3|wav|ogg|oga|flac|m4a|aac)$/i', $val)) {
            $fullPath = $this->getFullPath($val, $pages_id);
            $extension = pathinfo($val, PATHINFO_EXTENSION);
            list($icon, $iconClass) = $this->getFileTypeIcon($extension);
            $val = '<a title="Modal viewer" href="' . $fullPath . '" data-src="' . $fullPath . '" data-type="audio" class="image-thumb" data-filename="' . wire('config')->urls->httpRoot.ltrim(htmlspecialchars($fullPath), '/') . '">'
                 . htmlspecialchars($val) . '</a><a title="Download" href="' . $fullPath . '" download><span class="file-icon ' . $iconClass . '">' . $icon . '</span></a>';
        }
        elseif(preg_match('/\.(pdf|txt)$/i', $val) || preg_match('/^https?:\/\//i', $val)) {

            preg_match_all('/https?:\/\//i', $original, $matches);

            if (count($matches[0]) > 1) {
                return null;
            }

            if(preg_match('/^https?:\/\//i', $val)) {
                $fullPath = $original;
                $fullUrl = $original;
                $isLink = true;
            }
            else {
                $fullPath = $this->getFullPath($val, $pages_id);
                $fullUrl = wire('config')->urls->httpRoot.ltrim(htmlspecialchars($fullPath), '/');
                $isLink = false;
            }
            $extension = pathinfo($val, PATHINFO_EXTENSION);
            list($icon, $iconClass) = $this->getFileTypeIcon($extension);
            $val = '<a title="Modal viewer" href="' . $fullPath . '" data-src="' . $fullPath . '" data-type="iframe" class="image-thumb" data-filename="' . $fullUrl . '">'
                 . htmlspecialchars($original) . '</a><a title="'.($isLink ? 'View in new tab' : 'Download').'" '.($isLink ? ' target="_blank"' : ' download') . ' href="' . $fullPath . '"><span class="file-icon ' . $iconClass . '">' . $icon . '</span></a>';
        }
        elseif (preg_match('/\.(doc|docx|xls|xlsx|ppt|pptx|odt|ods|odp|zip|rar|7z|tar|gz|bz2)$/i', $val)) {
            $fullPath = $this->getFullPath($val, $pages_id);
            $fullUrl = wire('config')->urls->httpRoot . ltrim(htmlspecialchars($fullPath), '/');
            $extension = pathinfo($val, PATHINFO_EXTENSION);
            list($icon, $iconClass) = $this->getFileTypeIcon($extension);

            $val = '<a title="Modal viewer" href="' . $fullUrl . '" data-src="' . $fullUrl . '" data-type="download" class="image-thumb" data-filename="' . $fullUrl . '">'
                 . htmlspecialchars($val) . '</a><a title="Download" href="' . $fullPath . '" download><span class="file-icon ' . $iconClass . '">' . $icon . '</span></a>';
        }
        else {
            return null;
        }

        return $val;
    }

    public function formatMessageQuery(string $query, string $time, bool $failed = false) {
        wire('log')->save('adminer_queries', $query);
    }

    public function formatSqlCommandQuery(string $query) {
        wire('log')->save('adminer_queries', $query);
    }


    // helper functions
    private function formatPageStatus($val, $status) {
        $isUnpublished = $status & \ProcessWire\Page::statusUnpublished;
        $isHidden = $status & \ProcessWire\Page::statusHidden;
        $isTrash = $status & \ProcessWire\Page::statusTrash;
        return '<span style="' . ($isUnpublished ? 'text-decoration: line-through' : '') . ($isHidden ? '; opacity: 0.5' : '') . '">' . $val . ($isTrash ? ' 🗑︎' : '') . '</span>';
    }

    private function getFullPath($val, $pages_id) {
        if(preg_match('/^https?:\/\//i', $val)) {
            return $val;
        }
        return wire('config')->urls->files . $pages_id . '/' . $val;
    }

    private function getAvailableThumb($val, $pages_id) {
        $config = wire('config');
        $rootUrl = $config->urls->root;

        // check if $val is a full URL
        if (filter_var($val, FILTER_VALIDATE_URL)) {
            // if the URL does not belong to the same root, return an image icon
            if (strpos($val, $rootUrl) !== 0) {
                list($icon, $iconClass) = $this->getFileTypeIcon(pathinfo($val, PATHINFO_EXTENSION));
                return '<span class="file-icon ' . $iconClass . '">' . $icon . '</span>';
            }
        }

        // process local file normally
        $filePath = $config->urls->files . $pages_id . '/' . $val;
        $directory = dirname($filePath);
        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        $filepath1 = "{$directory}/{$filename}.0x{$this->gridSize2x}.{$extension}";
        $filepath2 = "{$directory}/{$filename}.{$this->gridSize2x}x0.{$extension}";
        $fullpath1 = $config->paths->root . $filepath1;
        $fullpath2 = $config->paths->root . $filepath2;

        $src = file_exists($fullpath1) ? $filepath1 : (file_exists($fullpath2) ? $filepath2 : $filePath);
        return '<img class="image-thumb-icon" src="' . $src . '" />';

    }

    private function getFileTypeIcon($fileExtension) {
        $fileExtension = strtolower($fileExtension);

        $icon = '📄';
        $class = '';

        if (in_array($fileExtension, ['doc', 'docx', 'odt', 'rtf'])) {
            $icon = '📝';
            $class = 'icon-doc';
        }
        elseif (in_array($fileExtension, ['xls', 'xlsx', 'ods', 'csv'])) {
            $icon = '📊';
            $class = 'icon-xls';
        }
        elseif (in_array($fileExtension, ['ppt', 'pptx', 'odp'])) {
            $icon = '📽️';
            $class = 'icon-ppt';
        }
        elseif ($fileExtension === 'pdf') {
            $icon = '📕';
            $class = 'icon-pdf';
        }
        elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp'])) {
            $icon = '🖼️';
            $class = '';
        }
        elseif (in_array($fileExtension, ['mp3', 'wav', 'ogg', 'oga', 'flac', 'm4a', 'aac'])) {
            $icon = '♪';
            $class = 'icon-media';
        }
        elseif (in_array($fileExtension, ['mp4', 'webm', 'ogg', 'ogv', 'mov', 'avi', 'wmv', 'mkv', 'flv'])) {
            $icon = '▶';
            $class = 'icon-media';
        }
        elseif (in_array($fileExtension, ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'])) {
            $icon = '🗜️';
            $class = 'icon-zip';
        }
        elseif (in_array($fileExtension, ['txt', 'json', 'xml', 'html', 'css', 'js', 'php', 'py', 'java', 'c', 'cpp', 'h', 'sh', 'rb'])) {
            $icon = '📝';
            $class = 'icon-code';
        }

        return [$icon, $class];
    }

}
