<?php
namespace WP\Base;

class FileHandler extends \SplFileObject {

    protected $_baseFile = null;

    public function __construct($fileName, $mode = 'r') {
        parent::__construct($fileName, $mode);
        $this->setInfoClass('\WP\Base\File');
    }

    public function gets() {
        return $this->fgets();
    }

    public function puts($str, $length = null) {
        return is_null($length) ? $this->fwrite($str) : $this->fwrite($str, $length);
    }


}

?>