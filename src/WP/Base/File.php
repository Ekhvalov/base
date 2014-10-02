<?php
namespace WP\Base;

class File extends \SplFileInfo
{
    const DS = DIRECTORY_SEPARATOR;

    protected static $_fileHandlerMethods = array(
        'rewind', 'eof', 'valid', 'fgets', 'gets', 'puts', 'fgetcsv',
        'setcsvcontrol', 'getcsvcontrol', 'flock', 'fflush', 'ftell',
        'fseek', 'fgetc', 'fpassthru', 'fgetss', 'fscanf', 'fwrite',
        'fstat', 'ftruncate', 'current', 'key', 'next', 'setflags',
        'getflags', 'setmaxlinelen', 'getmaxlinelen', 'seek', 'getcurrentline'
    );

    protected $_exists = false;
    protected $_fileName = '';
    protected $_realPath = '';
    protected $_handler = null;

    ////////// Constructors

    public function __construct($fileName) {
        parent::__construct($fileName);
        $this->setInfoClass('\WP\Base\File');
        $this->_fileName = $fileName;
        $this->_init();
    }

    public static function fromFilename($fileName) {
        return new static($fileName);
    }

    public static function fromHttpUpload($toDir, $fieldName = 'file', $newName = null) {
        if (!empty($_FILES[$fieldName]['error'])) {
            switch ($_FILES[$fieldName]['error']) {
                case '1':
                    throw new IOException("Uploaded file exceeds `upload_max_filesize` limit", 217);
                case '2':
                    $msg = "Uploaded file exceeds `MAX_FILE_SIZE` directive that was specified in the HTML form";
                    throw new IOException($msg, 218);
                default:
                    throw new IOException("Error while uploading", 219);
            }
        }
        $moveDir = rtrim($toDir, '/\\');
        $name = $newName ?: $_FILES[$fieldName]['name'];
        if (!file_exists($moveDir) || (!is_dir($moveDir))) {
            throw new IOException("Directory {$moveDir} are not exists", 301);
        }
        if (!is_writable($moveDir)) {
            throw new IOException("Directory {$moveDir} are not writable", 302);
        }
        $fullPath = $moveDir . self::DS . $name;
        if (@move_uploaded_file($_FILES[$fieldName]['tmp_name'], $fullPath)) {
            return new static($fullPath);
        } else {
            throw new IOException("Error moving uploaded file", 220);
        }
    }


    ////////// Magic

    public function __call($method, $args) {
        $method = strtolower($method);

        // Redirecting FileHandler methods calls to current handler
        if (in_array($method, self::$_fileHandlerMethods)) {
            if (is_null($this->_handler)) {
                throw new IOException("File is {$this->getPathname()} is not opened");
            }
            if (empty($args)) {
                return call_user_func(array($this->_handler, $method));
            }
            return call_user_func_array(array($this->_handler, $method), $args);
        }

        // openAs* methods
        if (substr($method, 0, 6) == 'openas') {
            $type = ucfirst(substr($method, 6));
            $className = __NAMESPACE__ . "\\{$type}FileHandler";
            $this->_handler = new $className($this->getPathname(), empty($args) ? 'r' : $args[0]);
            return $this;
        }

        throw new \BadMethodCallException("Class " . get_class($this) . " hasn't method {$method}");
    }


    ////////// Overrided methods

    public function getExtension() {
        return self::_getExt($this->_realPath);
    }

    public function openFile($mode = 'r') {
        ($mode == 'r') && $this->_checkExists();
        try {
            $this->_handler = new FileHandler($this->getPathname(), $mode);
        } catch (\RuntimeException $e) {
            throw new IOException($e->getMessage(), $e->getCode());
        }
        return $this;
    }

    ////////// Additional methods

    public function exists() {
        return $this->_exists;
    }

    public function getMimeType() {
        $mimetype = 'application/octet-stream';
        $this->_checkExists();
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimetype = finfo_file($finfo, $this->_realPath);
            finfo_close($finfo);
        }
        return $mimetype;
    }

    public function copyTo($newPath) {
        $this->_checkExists();
        if (!@copy($this->getPathname(), $newPath)) {
            throw new \RuntimeException("Can't copy {$this} to {$newPath}");
        }
        return new static($newPath);
    }

    public function moveTo($newPath) {
        $this->_checkExists();
        $newInstance = $this->copyTo($newPath);
        unlink($this->getPathname());
        return $newInstance;
    }

    public function drop() {
        if ($this->exists()) {
            @unlink($this->_realPath);
            $this->_init();
        }
        return $this;
    }

    public function unlink() {
        return $this->drop();
    }

    public function open($mode = 'r') {
        return $this->openFile($mode);
    }

    public function close() {
        $this->_handler = null;
    }

    public function contents($data = null) {
        if (!is_null($data)) {
            file_put_contents($this->getPathname(), $data);
            return $this;
        }
        $this->_checkExists();
        return file_get_contents($this->_realPath);
    }

    public function output() {
        $this->_checkExists();
        echo $this->contents();
        return $this;
    }

    public function httpOutput($forceDownload = false, $substituteName = null) {
        $this->_checkExists();
        $filename = $substituteName ?: $this->getFilename();
        $contentType = $forceDownload ? 'application/force-download' : $this->getMimeType();
        header("Content-Type: {$contentType}; name={$filename}");
        if ($forceDownload) {
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: {$this->getSize()}");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Expires: 0");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
        }
        $this->output();
        return $this;
    }


    ////////// Protected

    protected function _init() {
        $this->_exists = file_exists($this->_fileName) && $this->isFile();
        if ($this->_exists) {
            $this->_realPath = parent::getRealPath();
        }
    }

    protected function _checkExists() {
        if (!$this->_exists) {
            throw new IOException("File {$this->getPathname()} are not exists", 201);
        }
    }


    ////////// Static

    protected static function _getExt($filename) {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }
}
