<?php namespace AdminNeo;

use function ProcessWire\wire;

class ProcessWirePlugin extends Plugin {

    private $gridSize2x;

    // Fields fetched for every page referenced in a Select table.
    private $pageLabelFields;

    // Raw page data keyed by page id, filled in bulk by fillForeignDescriptions() so that
    // formatSelectionValue() never has to query per cell. See getPageRaw().
    private $pageCache = array();

    // Columns whose values are page ids in every table.
    const USER_ID_COLUMNS = array('uid', 'user_id', 'created_users_id', 'modified_users_id', 'user_created', 'user_updated');
    const PAGE_ID_COLUMNS = array('pid', 'pages_id', 'parent_id', 'parents_id', 'source_id', 'language_id', 'data');

    // Per-request memo for the field behind a field_* table and per-column page checks.
    private $selectField = false;
    private $pageIdColumnMemo = array();

    public function __construct() {

        $this->pageLabelFields = array('title', 'name', 'status', 'template');
        if(wire('modules')->isInstalled('PagePaths')) {
            $this->pageLabelFields[] = 'url';
        }

        $defaultGridSize = 130;
        $options = wire('config')->adminThumbOptions;
        if(!is_array($options)) $options = array();
        $gridSize = empty($options['gridSize']) ? $defaultGridSize : (int) $options['gridSize'];
        if($gridSize < 100) $gridSize = $defaultGridSize; // establish min of 100
        if($gridSize >= ($defaultGridSize * 2)) $gridSize = $defaultGridSize; // establish max of 259
        $this->gridSize2x = $gridSize * 2;

    }

    public function printToHead() {
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
                padding-right: 35px;
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


        <script<?= \TracyDebugger::getNonceAttr() ?>>
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


    public function getDatabases($flush = true) {
        return [wire('config')->dbName];
    }

    /**
     * AdminNeo calls this once with every row of a Select table before rendering any cell.
     *
     * formatSelectionValue() links page ids to their title/status, which used to mean two
     * queries per cell (a 50-row pages table made ~400 queries). Collect every page id from
     * the columns formatSelectionValue() treats as page references and fetch them all with
     * one findRaw() call; the cells then read from $this->pageCache.
     */
    public function fillForeignDescriptions(array $rows, array $foreignKeys) {
        if(!$rows || !isset($_GET['select']) || !method_exists(wire('pages'), 'findRaw')) {
            return $rows;
        }

        $ids = array();
        foreach($rows as $row) {
            foreach($row as $column => $value) {
                if($value === null || $value === '' || !$this->isPageIdColumn($column)) continue;
                foreach(explode(',', $value) as $v) {
                    $v = str_replace('pid', '', $v);
                    if(ctype_digit($v)) $ids[(int) $v] = true;
                }
            }
        }
        unset($ids[0]);

        if($ids) {
            $found = wire('pages')->findRaw('id=' . implode('|', array_keys($ids)) . ', include=all', $this->pageLabelFields);
            foreach($ids as $id => $unused) {
                // remember misses as well so a deleted id doesn't trigger a per-cell fallback query
                $this->pageCache[$id] = isset($found[$id]) ? $found[$id] : null;
            }
        }

        return $rows;
    }

    /**
     * Raw title/name/status/template(/url) for one page, from the cache filled by
     * fillForeignDescriptions() or, on a miss, from a single getRaw() call.
     */
    private function getPageRaw($id) {
        $id = (int) str_replace('pid', '', $id);
        if(!$id) return null;
        if(!array_key_exists($id, $this->pageCache)) {
            $this->pageCache[$id] = wire('pages')->getRaw('id=' . $id, $this->pageLabelFields);
        }
        return $this->pageCache[$id];
    }

    /**
     * The PW field behind the current field_* table, or null. Memoized per request.
     */
    private function getSelectField() {
        if($this->selectField === false) {
            $this->selectField = null;
            if(isset($_GET['select']) && strpos($_GET['select'], 'field_') === 0) {
                $f = wire('fields')->get(substr($_GET['select'], strlen('field_')));
                if($f) $this->selectField = $f;
            }
        }
        return $this->selectField;
    }

    /**
     * Whether a column of the current Select table holds page ids, using the same rules as
     * formatSelectionValue(). Memoized per column name.
     */
    private function isPageIdColumn($column) {
        if(isset($this->pageIdColumnMemo[$column])) return $this->pageIdColumnMemo[$column];

        $select = $_GET['select'];
        $isPage = false;

        if(in_array($select, array('fieldgroups', 'caches'))) {
            $isPage = false;
        }
        elseif($select == 'pages' && $column == 'id') {
            $isPage = true;
        }
        elseif(in_array($column, self::USER_ID_COLUMNS)) {
            $isPage = true;
        }
        elseif($column == 'data') {
            $f = $this->getSelectField();
            $isPage = $f && ($f->type instanceof \ProcessWire\FieldtypePage || $f->type instanceof \ProcessWire\FieldtypePageIDs || $f->type instanceof \ProcessWire\FieldtypeRepeater);
        }
        elseif(in_array($column, self::PAGE_ID_COLUMNS)) {
            // parent_id etc. link to pages everywhere except in the templates table
            $isPage = !($select == 'templates' && $column == 'id');
        }
        elseif(strpos($select, 'field_') !== false) {
            $isPage = $this->isPageSubfield($column);
        }

        $this->pageIdColumnMemo[$column] = $isPage;
        return $isPage;
    }

    /**
     * Whether a Table or Combo subfield column of the current field_* table is a page reference.
     */
    private function isPageSubfield($column) {
        $f = $this->getSelectField();
        if(!$f) return false;
        if($f->type instanceof \ProcessWire\FieldtypeTable) {
            $col = $f->type->getColumn($f, $column);
            return isset($col['type']) && strpos($col['type'], 'page') !== false;
        }
        if($f->type instanceof \ProcessWire\FieldtypeCombo) {
            return $f->getComboSettings()->getSubfieldType($column) === 'Page';
        }
        return false;
    }

    public function formatSelectionValue($val, $link, $field, $original) {

        // check if the current field is the pages_id column and store for use in other columns on the same row (ie to get image paths)
        static $pages_id = null;
        if ($field['field'] == 'pages_id') {
            $pages_id = $val;
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
                $template = wire('templates')->get((int) $val);
                $name = $template ? $template->get('label|name') : '';
                if($name) {
                    $val = '<a href="'.wire('config')->urls->admin.'setup/template/edit/?id='.$val.'" target="_parent" title="'.$name.'">'.$val.'</a>';
                }
            }
            elseif($_GET['select'] == 'fields' && $field['field'] == 'id') {
                $val = '<a href="'.wire('config')->urls->admin.'setup/field/edit/?id='.$val.'" target="_parent">'.$val.'</a>';
            }
            elseif(in_array($field['field'], array('field_id', 'fields_id'))) {
                $f = wire('fields')->get((int) $val);
                if($f) {
                    $name = $f->get('label|name');
                    $val = '<a href="'.wire('config')->urls->admin.'setup/field/edit/?id='.$val.'" target="_parent" title="'.$name.'">'.$val.'</a>';
                }
            }
            elseif($_GET['select'] == 'pages' && $field['field'] == 'id') {
                if(method_exists(wire('pages'), 'getRaw')) {
                    $name = $this->getPageRaw($val);
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
            elseif(in_array($field['field'], self::USER_ID_COLUMNS)) {
                if(method_exists(wire('pages'), 'getRaw')) {
                    $name = $this->getPageRaw($val);
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

                $f = $this->getSelectField();
                if($this->isPageSubfield($field['field'])) {
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
                                $name = $this->getPageRaw($v);
                                if($name) {
                                    $v_with_status = $this->formatPageStatus($v, $name['status']);
                                    $name = (isset($name['title']) ? $name['title'] : $name['name']) . (isset($name['url']) ? ' ('.$name['url'].')' : '') . (isset($name['template']) ? ' ('.$name['template']['name'].')' : '');
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
            $safeFullPath = htmlspecialchars($fullPath ?? '', ENT_QUOTES, 'UTF-8');
            $val = '<a title="Modal viewer" href="' . $safeFullPath . '" data-src="' . $safeFullPath . '" data-type="image" class="image-thumb" data-filename="' . wire('config')->urls->httpRoot.ltrim($safeFullPath, '/') . '">'
                 . htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8') . '</a><a title="Download" href="' . $safeFullPath . '" download>'.$thumb.'</a>';
        }
        elseif(preg_match('/\.(mp4|webm|ogg|ogv|mov|avi|wmv|mkv|flv)$/i', $val)) {
            $fullPath = $this->getFullPath($val, $pages_id);
            $safeFullPath = htmlspecialchars($fullPath ?? '', ENT_QUOTES, 'UTF-8');
            $extension = pathinfo($val, PATHINFO_EXTENSION);
            list($icon, $iconClass) = $this->getFileTypeIcon($extension);
            $val = '<a title="Modal viewer" href="' . $safeFullPath . '" data-src="' . $safeFullPath . '" data-type="video" class="image-thumb" data-filename="' . wire('config')->urls->httpRoot.ltrim($safeFullPath, '/') . '">'
                 . htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8') . '</a><a title="Download" href="' . $safeFullPath . '" download><span class="file-icon ' . $iconClass . '">' . $icon . '</span></a>';
        }
        elseif(preg_match('/\.(mp3|wav|ogg|oga|flac|m4a|aac)$/i', $val)) {
            $fullPath = $this->getFullPath($val, $pages_id);
            $safeFullPath = htmlspecialchars($fullPath ?? '', ENT_QUOTES, 'UTF-8');
            $extension = pathinfo($val, PATHINFO_EXTENSION);
            list($icon, $iconClass) = $this->getFileTypeIcon($extension);
            $val = '<a title="Modal viewer" href="' . $safeFullPath . '" data-src="' . $safeFullPath . '" data-type="audio" class="image-thumb" data-filename="' . wire('config')->urls->httpRoot.ltrim($safeFullPath, '/') . '">'
                 . htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8') . '</a><a title="Download" href="' . $safeFullPath . '" download><span class="file-icon ' . $iconClass . '">' . $icon . '</span></a>';
        }
        elseif(preg_match('/\.(pdf|txt)$/i', $val) || preg_match('/^https?:\/\//i', $val)) {

            preg_match_all('/https?:\/\//i', $original, $matches);

            if (count($matches[0]) > 1) {
                return null;
            }

            if(preg_match('/^https?:\/\//i', $val)) {
                $fullPath = $original;
                $fullUrl = htmlspecialchars($original ?? '', ENT_QUOTES, 'UTF-8');
                $isLink = true;
            }
            else {
                $fullPath = $this->getFullPath($val, $pages_id);
                $fullUrl = wire('config')->urls->httpRoot.ltrim(htmlspecialchars($fullPath ?? '', ENT_QUOTES, 'UTF-8'), '/');
                $isLink = false;
            }
            $safeFullPath = htmlspecialchars($fullPath ?? '', ENT_QUOTES, 'UTF-8');
            $extension = pathinfo($val, PATHINFO_EXTENSION);
            list($icon, $iconClass) = $this->getFileTypeIcon($extension);
            $val = '<a title="Modal viewer" href="' . $safeFullPath . '" data-src="' . $safeFullPath . '" data-type="iframe" class="image-thumb" data-filename="' . $fullUrl . '">'
                 . htmlspecialchars($original ?? '', ENT_QUOTES, 'UTF-8') . '</a><a title="'.($isLink ? 'View in new tab' : 'Download').'" '.($isLink ? ' target="_blank"' : ' download') . ' href="' . $safeFullPath . '"><span class="file-icon ' . $iconClass . '">' . $icon . '</span></a>';
        }
        elseif (preg_match('/\.(doc|docx|xls|xlsx|ppt|pptx|odt|ods|odp|zip|rar|7z|tar|gz|bz2)$/i', $val)) {
            $fullPath = $this->getFullPath($val, $pages_id);
            $fullUrl = wire('config')->urls->httpRoot . ltrim(htmlspecialchars($fullPath ?? '', ENT_QUOTES, 'UTF-8'), '/');
            $extension = pathinfo($val, PATHINFO_EXTENSION);
            list($icon, $iconClass) = $this->getFileTypeIcon($extension);

            $val = '<a title="Modal viewer" href="' . $fullUrl . '" data-src="' . $fullUrl . '" data-type="download" class="image-thumb" data-filename="' . $fullUrl . '">'
                 . htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8') . '</a><a title="Download" href="' . $fullPath . '" download><span class="file-icon ' . $iconClass . '">' . $icon . '</span></a>';
        }
        else {
            return null;
        }

        return $val;
    }

    public function formatMessageQuery($query, $time, $failed = false) {
        wire('log')->save('adminer_queries', $query);
    }

    public function formatSqlCommandQuery($query) {
        wire('log')->save('adminer_queries', $query);
    }


    // helper functions
    private function formatPageStatus($val, $status) {
        $isUnpublished = $status & \ProcessWire\Page::statusUnpublished;
        $isHidden = $status & \ProcessWire\Page::statusHidden;
        $isTrash = $status & \ProcessWire\Page::statusTrash;
        return '<span style="' . ($isUnpublished ? 'text-decoration: line-through; ' : '') . ($isHidden ? 'opacity: 0.5' : '') . '">' . ($isTrash ? '🗑︎ ' : '') . $val . '</span>';
    }

    private function getFullPath($val, $pages_id) {
        if(preg_match('/^https?:\/\//i', $val)) {
            return $val;
        }
        $filesUrl = wire('config')->urls->files;
        if(strpos($val, ltrim($filesUrl, '/')) === 0 || strpos($val, $filesUrl) === 0) {
            return '/' . ltrim($val, '/');
        }
        return $filesUrl . $pages_id . '/' . $val;
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
        $filesUrl = $config->urls->files;
        if(strpos($val, ltrim($filesUrl, '/')) === 0 || strpos($val, $filesUrl) === 0) {
            $filePath = '/' . ltrim($val, '/');
        }
        else {
            $filePath = $filesUrl . $pages_id . '/' . $val;
        }
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
