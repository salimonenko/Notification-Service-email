<?php
// Рекурсивно просматриваем все файлы проекта, находим там символы + + + (без пробелов), собираем эти данные и выдаем клиенту на экран с указанием строки в конкретном файле, где эти символы обнаружились.

if(!defined('PATH_ABSOLUTE')){die('Forbidden.');} // Запрет непосредственного доступа к этому модулю
require_once PATH_ABSOLUTE . '/routes/check_access.php'; // Запрет непосредственного доступа к этому модулю

require_once '../config/determine_absolute_PATH.php'; // Определяем абсолютный путь до основного каталога

$path = PATH_ABSOLUTE; // Путь до начального каталога, определяемого константой THIS_DIR
$path_relative = basename($path);
$entry = '';

echo '<table><tbody>';
echo '<tr><td><p title="Каталог" style="background-color: #ffe38e; display: table; margin-top: 0">'.$path_relative .'</p></td><td>Строчки, где содержатся символы <b>+'.'++</b> :</td></tr>';


look_dir($path, $path_relative, 0, $entry);


function look_dir($path, $path_relative, $i_num, $entry){

    chdir($path);
    if (($handle = opendir($path)) ){

        while (false !== ($entry = readdir($handle))) {

                if (is_dir($entry) ) { // Если каталог
                    if ((($entry == ".") || ($entry == "..") )) {
                        continue;
                    }

                    $entry = realpath($entry);

$val = preg_replace('/^(.*)'. $path_relative .'/', $path_relative, str_replace('\\', '/', $entry));
$i_num = substr_count($val, '/');

echo '<tr><td><p title="Каталог" style="background-color: #ffe38e; display: table; margin-top: 10px; margin-left: '. (15*$i_num). 'px">' . $val.'</p></td><td></td></tr>';


                    look_dir($entry, $path_relative, $i_num, $entry);


                }else{ // Если файл

                    $line = '';
                    if(file_exists(realpath($entry))){
                        $str_Arr = file(realpath($entry));
                        for($i=0; $i < sizeof($str_Arr); $i++){
                            if(substr_count($str_Arr[$i], '+'.'+'.'+') > 0){
                                if($line){
                                    $z = ', ';
                                }else{
                                    $z = '';
                                }
                                $line = $line. $z. ($i+1);
                            }
                        }
                    }

                    $line = $line ? 'Строчки '. $line : '';
// Имена некоторых файлов отображаются с завышенным отступом, создавая впечатление, будто бы, из другого каталога +++
echo '<tr><td><p title="Файл" style="display: block; background-color: #9bf3ff; margin-left: '. (15*($i_num+0.5)). 'px;">'. '<span>'. basename($entry). '</span></p></td><td>'. $line. '</td></tr>';
                }

        }
        closedir($handle);
        chdir('..');
    }else{
        echo 'Каталог '. realpath($entry). ' не может быть открыт.';
    }
}

die('</tbody></table>');

