<?php

namespace BaseFrame\System;

use BaseFrame\Exception\Domain\ParseFatalException;

// обертка по работе с файлами
class File {

    // путь до файла
    protected string $_file_path;

    /**
     * Конструктор
     * 
     * @param string $workd_dir Абсолютный путь до каталога, в которой ведется работа с файлом
     * @param string $file_subpath Путь до файла в рабочей папке
     */
    private function __construct(string $work_dir, string $file_subpath) {

        // узнаем, куда именно ведет путь
        $work_dir = $this->_normalizeAbsolutePath($work_dir);

        // чит код в виде работы в корне файловой системы отменяем
        if ($work_dir == "/") {
            throw new ParseFatalException("can`t write in root dir");
        }

        // не принимаем относительных путей
        if (!$work_dir) {
            throw new ParseFatalException("path must be absolute");
        }

        // склеиваем путь до рабочей директории и путь до файла
        $file_path = $this->_normalizeAbsolutePath("$work_dir/$file_subpath");

        // проверяем, что мы остались в рабочей директории
        if (!str_starts_with($file_path, $work_dir)) {
            throw new ParseFatalException("file subpath doesnt`t belong work dir");
        }

        $this->_file_path = $file_path;
    }

    /**
     * Статический метод инита для удобства вызова
     */
    public static function init(string $work_dir, string $file_subpath):self {

        return new static($work_dir, $file_subpath);
    }

    /**
     * Прочитать файл
     */
    public function read():string {

        return file_get_contents($this->_file_path);
    }

    /**
     * Существует ли файл
     */
    public function isExists():bool {
        
        return file_exists($this->_file_path);
    }

    /**
     * Записать в файл
     * 
     * @param string $content Что записываем
     */
    public function write(mixed $content, bool $append = false):self {

        $flags = 0;
        if ($append) {
            $flags |= FILE_APPEND;
        }

        file_put_contents($this->_file_path, $content, $flags);

        return $this;
    }

    /**
     * Удалить файл
     */
    public function delete():void {

        unlink($this->_file_path);
    }

    /**
     * Установить права на файл
     * 
     * @param int $permissions Устанавливаемые права - в восьмеричной системе счисления (0755)
     */
    public function chmod(int $permissions):void {

        chmod($this->_file_path, $permissions);
    }

    /**
     * Получить путь до файла
     * 
     * @return string
     */
    public function getFilePath():string {
        
        return $this->_file_path;
    }

    /**
     * Скопировать файл по другому пути
     * 
     * @param File $dst_file файл назначение
     * 
     * @return bool
     */
    public function copy(File $dst_file):bool {

        return copy($this->_file_path, $dst_file->getFilePath());
    }

    /**
     * Нормализовать абсолютный путь
     * Работает для всех систем (Windows, UNIX)
     * Взято из https://www.php.net/manual/ru/function.realpath.php#124254
     * Изначальная функция изменена, чтобы она не работала с относительными путями
     * Функция используется взамен realpath, так как последняя работает только с существующими файлами
     * 
     * @param string $absolute_path Абсолютный путь
     * 
     * @return string|false
     * Возвращает false, если переданный путь не является абсолютным
     */
    protected function _normalizeAbsolutePath(string $absolute_path):string|false {

        // очищаем путь в зависимости от системы
        $path = mb_ereg_replace("\\\\|/", DIRECTORY_SEPARATOR, $absolute_path, "msr");

        // проверяем, что путь начинается с сепаратора (UNIX)
        $start_with_separator = $path[0] === DIRECTORY_SEPARATOR;

        // проверяем, что путь начинается с буквы диска (Windows)
        preg_match('/^[a-zA-Z]:/', $path, $matches);
        $start_with_letter_dir = isset($matches[0]) ? $matches[0] : false;

        // если путь не начинается ни с сепаратора, ни с буквы диска - считаем, что нам пытаются скормить относительный путь
        // отклоняем такое и возвращаем false
        if (!$start_with_separator && !$start_with_letter_dir) {
            return false;
        }

        // делим путь на составные части по сепаратору и отсеиваем пустые
        $sub_path_list = array_filter(explode(DIRECTORY_SEPARATOR, $path));

        // букву диска сразу отметаем из массива, если она там есть
        // в конце функции мы приклеим ее обратно
        if ($start_with_letter_dir) {
            array_shift($sub_path_list);
        }

        $absolutes = [];
        foreach ($sub_path_list as $sub_path) {

            // если в пути точка - пропускаем       
            if ('.' === $sub_path) {
                continue;
            }

            // если две точки - удаляем предыдущий кусок абсолютного пути, так как по сути прыгнули в родительский каталог
            if ('..' === $sub_path) {
                array_pop($absolutes);
                continue;
            }

            // добавляем кусок пути в массив
            $absolutes[] = $sub_path;
        }

        // склеиваем абсолютный путь
        if ($start_with_separator) {
            return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $absolutes);
        }

        return $start_with_letter_dir . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $absolutes);
    }
}