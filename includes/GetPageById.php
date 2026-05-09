<?php namespace ProcessWire;

$p = $this->wire('pages')->get((int)$_POST['goToPage']);
$pageInfo = array();
if($p->id) {
    // Decode any pre-encoded entities (PW's HtmlEntityEncoder text formatter
    // can store titles like "A &amp; B"). The client renders via textContent,
    // so we want plain characters here, not HTML-encoded ones.
    $rawTitle = html_entity_decode((string)($p->title ?: $p->name ?: ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $pageInfo['id'] = $p->id;
    $pageInfo['title'] = truncateText(strip_tags($rawTitle), 50);
    $pageInfo['url'] = $p->url;
    $pageInfo['template_id'] = $p->template->id;
    $pageInfo['template_name'] = $p->template->name;
    $pageInfo['template_label'] = html_entity_decode((string)($p->template->label ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $pageInfo['path'] = $p->path;
    $pageInfo['unpublished'] = $p->isUnpublished();
    $pageInfo['hidden'] = $p->isHidden();
    $pageInfo['trash'] = $p->isTrash();
}
echo json_encode($pageInfo);
exit;

function truncateText($text, $maxlength) {
    if(function_exists('mb_strlen')) {
        if(mb_strlen($text, 'UTF-8') <= $maxlength) return $text;
        $truncated = mb_substr($text, 0, $maxlength, 'UTF-8');
        $lastSpace = mb_strrpos($truncated, ' ', 0, 'UTF-8');
        if($lastSpace !== false) $truncated = mb_substr($truncated, 0, $lastSpace, 'UTF-8');
        return rtrim($truncated, " .!?,;") . '…';
    }
    if(strlen($text) <= $maxlength) return $text;
    $truncated = substr($text, 0, $maxlength);
    $lastSpace = strrpos($truncated, ' ');
    if($lastSpace !== false) $truncated = substr($truncated, 0, $lastSpace);
    return rtrim($truncated, " .!?,;") . '…';
}
