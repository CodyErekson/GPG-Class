<?php
//this is the class used to handle extracting data from GPG encrypted files

class gpg {

    public $gid; //the key id
    private $passphrase; //the passphrase
    private $gpg;
    private $keys;
    public $error = array();

    public function __construct($keyring, $gid, $passphrase){
        putenv('GNUPGHOME=' . $keyring);
        $this->gid = $gid;
        $this->passphrase = $passphrase;
        $this->gpg = new gnupg();
        $this->gpg->seterrormode(gnupg::ERROR_EXCEPTION);
        //get the keys
        try {
            $this->keys = $this->gpg->keyinfo($this->gid);
            return true;
        } catch (Exception $e) {
            $this->doError($e->getMessage());
            return false;
        }
    }

    public function decrypt($filename){
        //open and read the given file, decrypting the contents
        if ( file_exists($filename) ){
            $fh = fopen($filename, "r");
            $contents = fread($fh, filesize($filename));
            fclose($fh);
        } else {
            $this->doError($filename . " does not exist.");
            return false;
        }
        try {
            $this->gpg->adddecryptkey($this->gid, $this->passphrase);
            $text = $this->gpg->decrypt($contents);
            return $text;
        } catch (Exception $e) {
            $this->doError($e->getMessage());
            return false;
        }
    }

    private function doError($error){
        $this->error[] = $error;
    }

}

?>
