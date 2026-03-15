<?php
// painel_layout.php — micro helper: builds the full sidebar HTML for any panel page
// Usage: define $titulo_pag before including header_painel.php

function pLink(string $ico, string $href, string $label, string $badge=''): string {
    $script = str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']??'')).'/'.basename($_SERVER['SCRIPT_NAME']??'');
    $active = (parse_url($href,PHP_URL_PATH) === $script) ? ' active' : '';
    $bdg    = $badge ? "<span class='badge bg-danger ms-auto' style='font-size:.62rem;'>$badge</span>" : '';
    return "<a class='nav-item{$active}' href='".h($href)."'><i data-feather='{$ico}'></i><span>{$label}</span>{$bdg}</a>";
}
