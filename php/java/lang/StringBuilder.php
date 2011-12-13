<?php

namespace java\lang;

/**
 * A port of java.lang.StringBuilder, (but also with most String methods). It probably has bugs.
 */
class StringBuilder {
    /**
     * @var string
     */
    protected $utf8Bytes;

    /**
     * @param mixed $val
     */
    public function __construct($val = null)
    {
        if ($val === null) {
            $this->utf8Bytes = '';
        }
        if (is_bool($val)) {
            $this->utf8Bytes = $val ? 'true' : 'false';
        } else {
            $this->utf8Bytes = ($val instanceof StringBuilder)
                ? $val->getBytes()
                : (string) $val;
        }
    }

    /**
     * @param StringBuilder $val
     * @param int $start
     * @param int $end
     * @return StringBuilder
     */
    public function append(StringBuilder $val, $start = 0, $end = null)
    {
        $this->utf8Bytes .= $val->substring($start, $end)->getBytes();
        return $this;
    }

    /**
     * @param int $offset
     * @param StringBuilder $str
     * @param int $start
     * @param int $end
     * @return StringBuilder
     */
    public function insert($offset, StringBuilder $str, $start = 0, $end = null)
    {
        $this->utf8Bytes = mb_substr($this->utf8Bytes, 0, $offset, 'UTF-8')
            . $str->substring($start, $end)->getBytes()
            . mb_substr($this->utf8Bytes, $offset, null, 'UTF-8');
        return $this;
    }

    /**
     * @param int $start
     * @param int $end
     * @return StringBuilder
     */
    public function delete($start, $end)
    {
        $this->utf8Bytes = mb_substr($this->utf8Bytes, 0, $start, 'UTF-8')
            . mb_substr($this->utf8Bytes, $end, null, 'UTF-8');
        return $this;
    }

    /**
     * Get UTF-8 bytes
     * @return string
     */
    public function getBytes()
    {
        return $this->utf8Bytes;
    }

    /**
     * @param int $index
     * @return string
     */
    public function charAt($index)
    {
        return mb_substr($this->utf8Bytes, $index, 1, 'UTF-8');
    }

    /**
     * @param int $index
     * @return int
     */
    public function codePointAt($index)
    {
        $char = $this->charAt($index);
        $arr = unpack("N", mb_convert_encoding($char, 'UCS-4BE', 'UTF-8'));
        return array_pop($arr);
    }

    /**
     * @param StringBuilder $str
     * @return StringBuilder
     */
    public function concat(StringBuilder $str) {
        return new StringBuilder($this->utf8Bytes . $str->getBytes());
    }

    /**
     * @param StringBuilder $s
     * @return bool
     */
    public function contains(StringBuilder $s)
    {
        return false !== strpos($this->utf8Bytes, $s->getBytes());
    }

    /**
     * @param StringBuilder $str
     * @return bool
     */
    public function equals(StringBuilder $str)
    {
        return $this->utf8Bytes === $str->getBytes();
    }

    /**
     * @param StringBuilder $str
     * @param int $fromIndex
     * @return int
     */
    public function indexOf(StringBuilder $str, $fromIndex = 0)
    {
        $ret = mb_strpos($this->utf8Bytes, $str->getBytes(), $fromIndex, 'UTF-8');
        return ($ret === false) ? -1 : $ret;
    }

    /**
     * @param StringBuilder $str
     * @param int $fromIndex
     * @return int
     */
    public function lastIndexOf(StringBuilder $str, $fromIndex = 0)
    {
        $ret = mb_strrpos($this->utf8Bytes, $str->getBytes(), $fromIndex, 'UTF-8');
        return ($ret === false) ? -1 : $ret;
    }

    /**
     * @return int
     */
    public function length()
    {
        return mb_strlen($this->utf8Bytes, 'UTF-8');
    }

    /**
     * @param int $beginIndex
     * @param int $endIndex
     * @return StringBuilder
     */
    public function substring($beginIndex, $endIndex = null)
    {
        $length = ($endIndex === null) ? null : ($endIndex - $beginIndex);
        return new StringBuilder(mb_substr($this->utf8Bytes, $beginIndex, $length, 'UTF-8'));
    }

    /**
     * @return StringBuilder
     */
    public function toLowerCase()
    {
        return new StringBuilder(mb_strtolower($this->utf8Bytes, 'UTF-8'));
    }

    /**
     * @return StringBuilder
     */
    public function toUpperCase()
    {
        return new StringBuilder(mb_strtoupper($this->utf8Bytes, 'UTF-8'));
    }

    /**
     * @return StringBuilder
     */
    public function trim()
    {
        return new StringBuilder(trim($this->utf8Bytes));
    }

    /**
     * @throws \BadMethodCallException
     * @param StringBuilder $regex
     * @param StringBuilder $replacement
     * @return void
     */
    public function replaceAll(StringBuilder $regex, StringBuilder $replacement)
    {
        throw new \BadMethodCallException('method not implemented.');
    }

    /**
     * @param mixed $val
     * @return StringBuilder
     */
    static public function valueOf($val)
    {
        return new StringBuilder($val);
    }
}
