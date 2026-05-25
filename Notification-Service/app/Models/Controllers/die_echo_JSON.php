<?php

// Делает вывод (ответ) клиенту в виде echo или die в формате JSON
function die_echo_JSON($text_mess, $asArray, $flag_die) {
    /*     $text_mess - текстовое сообщение, которое будет выводиться на экран клиенту в формате JSON
           $asArray   - 0 (тогда выводимый JSON будет состоять из множества свойств, равных числу символов в сообщении);
                        1 (тогда JSON будет цельным в виде одного свойства)
           $flag_die  - 0 (будет использована функция echo)
                        1 (будет использована функция die() )
    */
    if ($asArray) {
        $text_mess = array($text_mess);
    }

    Exception_response(json_encode($text_mess), $flag_die);

}