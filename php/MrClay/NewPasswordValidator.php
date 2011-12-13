<?php

namespace MrClay;

class NewPasswordValidator {

    const REASON_UNKNOWN = 'unknown';
    const REASON_TOO_SHORT = 'too short';
    const REASON_FOUND_IN_LIST = 'found in list';
    const REASON_TOO_WEAK = 'too weak';
    const REASON_INSUFFICIENT_ENTROPY = 'insufficient entropy';
    const REASON_FAILED_VALIDATOR = 'failed validator';

    /**
     * @var array
     */
    protected $_passwordLists = array();

    /**
     * @var int
     */
    protected $_minLength = 0;

    /**
     * @var float
     */
    protected $_minStrength = 0;

    /**
     * @var float
     */
    protected $_minEntropy = 0;

    /**
     * @var string
     */
    protected $_reasonType = '';

    /**
     * @var array
     */
    protected $_reasonDetails = array();

    /**
     * @var array
     */
    protected $_validators = array();

    /**
     * @throws \Exception
     * @param string|array $file
     * @return NewPasswordValidator
     */
    public function addPasswordList($file)
    {
        if (is_array($file)) {
            foreach ($file as $list) {
                $this->addPasswordList($list);
            }
            return $this;
        }
        if (is_file($file) && is_readable($file)) {
            $this->_passwordLists[] = $file;
            return $this;
        } else {
            throw new \Exception('$file not readable: ' . $file);
        }
    }

    /**
     * @throws \Exception
     * @param string $dir
     * @param string $extension
     * @return NewPasswordValidator
     */
    public function addPasswordListsFromDir($dir = null, $extension = '.txt')
    {
        if (! $dir) {
            $dir = __DIR__ . '/NewPasswordValidator/lists';
        }
        $dir = rtrim($dir, '/\\');
        if (! is_dir($dir) || ! is_readable($dir)) {
            throw new \Exception('$dir is not a readable directory: ' . $dir);
        }
        $d = dir($dir);
        $i = 0;
        while ($entry = $d->read()) {
            if ($entry[0] !== '.'
                && is_file($dir . DIRECTORY_SEPARATOR . $entry)
                && $extension === substr($entry, - strlen($extension))
            ) {
                $this->addPasswordList($dir . DIRECTORY_SEPARATOR . $entry);
                $i += 1;
            }
        }
        if ($i == 0) {
            throw new \Exception('$dir did not contain any matching files: ' . $dir);
        }
        return $this;
    }

    /**
     * Add a 3rd party validator object/callable whose isValid() method/invocation must return true.
     * @param mixed $validator callable or object with isValid method
     * @return NewPasswordValidator
     */
    public function addValidator($validator)
    {
        if (is_callable($validator)) {
            $this->_validators[] = array('callable', $validator);
        } elseif (is_object($validator) && method_exists($validator, 'isValid')) {
            $this->_validators[] = array('validator', $validator);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getReason()
    {
        return array($this->_reasonType, $this->_reasonDetails);
    }

    /**
     * @param int $byteLength length in bytes
     * @return NewPasswordValidator
     */
    public function setMinimumLength($byteLength = 6)
    {
        $this->_minLength = $byteLength;
        return $this;
    }

    /**
     * @param int|float $strength
     * @return NewPasswordValidator
     */
    public function setMinimumStrength($strength = 10)
    {
        $this->_minStrength = $strength;
        return $this;
    }

    /**
     * @param int|float $entropy
     * @return NewPasswordValidator
     */
    public function setMinimumEntropy($entropy = 14)
    {
        $this->_minEntropy = $entropy;
        return $this;
    }

    /**
     * If returns false, you may call getReason() to find the reason validation failed
     * @param string $password
     * @return bool
     */
    public function isValid($password)
    {
        if ($this->_minLength) {
            $len = strlen($password);
            if ($len < $this->_minLength) {
                return $this->_setInvalidReason(self::REASON_TOO_SHORT, array(
                    'minimum length' => $this->_minLength,
                    'password length' => $len,
                ));
            }
        }
        if ($this->_minStrength) {
            $strength = $this->calculateStrength($password);
            if ($strength < $this->_minStrength) {
                return $this->_setInvalidReason(self::REASON_TOO_WEAK, array(
                    'minimum strength' => $this->_minStrength,
                    'password strength' => $strength,
                ));
            }
        }
        if ($this->_minEntropy) {
            $entropy = $this->calculateEntropy($password);
            if ($entropy < $this->_minEntropy) {
                return $this->_setInvalidReason(self::REASON_INSUFFICIENT_ENTROPY, array(
                    'minimum entropy' => $this->_minEntropy,
                    'password entropy' => $entropy,
                ));
            }
        }
        if ($this->_passwordLists) {
            $file = $this->findPassword($password);
            if ($file) {
                return $this->_setInvalidReason(self::REASON_FOUND_IN_LIST, array(
                    'list file' => $file,
                ));
            }
        }
        foreach ($this->_validators as $validator) {
            list($type, $thing) = $validator;
            if ($type === 'callable') {
                $func = $thing;
            } else {
                $func = array($thing, 'isValid');
            }
            $result = call_user_func($func, $password);
            if (! $result) {
                return $this->_setInvalidReason(self::REASON_FAILED_VALIDATOR, array(
                    'validator' => $thing,
                ));
            }
        }
        return true;
    }

    /**
     * @param string $type
     * @param array $details
     * @return false
     */
    protected function _setInvalidReason($type = self::REASON_UNKNOWN, array $details = array())
    {
        $this->_reasonType = $type;
        $this->_reasonDetails = $details;
        return false;
    }

    /**
     * @param string $listFile
     * @param string $password
     * @return bool
     */
    public function listContainsPassword($listFile, $password)
    {
        $command = "fgrep -x -m 1 " . escapeshellarg($password) . " " . escapeshellarg($listFile);
        $result = shell_exec($command);
        return (is_string($result) && trim($result) !== '');
    }

    /**
     * @throws Exception
     * @param string $password
     * @return string|bool the file location the password was found in or false
     */
    public function findPassword($password)
    {
        if (empty($this->_passwordLists)) {
            throw new \Exception('There are no password files to search.');
        }
        $this->_passwordLists = array_unique($this->_passwordLists);
        foreach ($this->_passwordLists as $file) {
            if ($this->listContainsPassword($file, $password)) {
                return $file;
            }
        }
        return false;
    }

    /**
     * @param string $password
     * @return float
     */
    public function calculateStrength($password)
    {
        $strength = strlen($password);
        if (strtolower($password) !== $password) {
            $strength += 1;
        }
        if (strtoupper($password) !== $password) {
            $strength += 1;
        }
        if (preg_match('/[0-9]/', $password)) {
            $strength += 1;
        }
        preg_match_all('/[^a-zA-Z0-9]/', $password, $nonAlphaNumeric);
        $strength += count($nonAlphaNumeric[0]);
        $chars = str_split($password);
        // deduct a little for each re-use of a char
        sort($chars);
        $last = null;
        foreach ($chars as $char) {
            if ($char === $last) {
                $strength -= .5;
            }
            $last = $char;
        }
        return $strength;
    }

    /**
     * @param string $password
     * @return float
     * @link http://en.wikipedia.org/wiki/Password_strength#Human-generated_passwords
     */
    public function calculateEntropy($password)
    {
        $len = strlen($password);
        $entropy = 0;
        if ($len) {
            $entropy += 4;
        }
        if ($len > 1) {
            $entropy += max($len - 1, 7) * 2;
        }
        if ($len > 8) {
            $entropy += max($len - 8, 12) * 1.5;
        }
        if ($len > 20) {
            $entropy += ($len - 20) * 1;
        }
        if (preg_match('/[A-Z]/', $password) && preg_match('/[^a-zA-Z]/', $password)) {
            $entropy += 6;
        }
        return $entropy;
    }
}