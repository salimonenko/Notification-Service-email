<?php
// Проверка соответствия типов данных, исходя из имеющихся в БД MySQL. Потом нужно добавить проверки на дополнительные типы данных +++

function int_types_MySQL($type) {
// Целочисленные типы данных, имеющиеся в MySQL
    $int_types_MySQL = array('tinyint', 'smallint', 'mediumint', 'int', 'bigint'); // Возможные целые типы в базе данных

    return in_array(strtolower($type), $int_types_MySQL);
}

function float_types_MySQL($type) {
// Нецелочисленные типы данных, имеющиеся в MySQL
    $int_types_MySQL = array('float', 'double', 'decimal', 'numeric', 'numeric', 'fixed', 'real'); // Возможные целые типы в базе данных

    return in_array(strtolower($type), $int_types_MySQL);
}
