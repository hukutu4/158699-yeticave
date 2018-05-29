<?php

/** Шаблонизатор
 * @param string $filename
 * @param array $params
 * @return string
 */
function renderTemplate($filename, $params = []) {
    if (file_exists($filename)) {
        extract($params);
        ob_start();
        require $filename;
        return ob_get_clean();
    }
    return '';
}

/** Форматирует целое число, представляя его в виде строки, с разделителем пробелом для тысяч, дополненным знаком рубля.
 * @param int $price
 * @return string
 */
function rurNumberFormat(int $price) {
    return number_format($price, 0, '.', ' ') . '<b class="rub">р</b>';
}