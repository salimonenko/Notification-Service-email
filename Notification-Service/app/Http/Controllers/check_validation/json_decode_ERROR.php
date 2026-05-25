<?php

function json_decode_ERROR(){

    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $er_dec = '';
            break;
        case JSON_ERROR_DEPTH:
            $er_dec = 'Достигнута максимальная глубина стека json_decode';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $er_dec = 'Некорректные разряды или несоответствие режимов json_decode';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $er_dec = 'Некорректный управляющий символ в json_decode';
            break;
        case JSON_ERROR_SYNTAX:
            $er_dec = 'Синтаксическая ошибка, некорректный JSON';
            break;
        case JSON_ERROR_UTF8:
            $er_dec = 'Некорректные символы UTF-8, возможно неверно закодирован';
            break;
        default:
            $er_dec = 'Неизвестная ошибка json_decode';
            break;
    }
    return $er_dec;
}

