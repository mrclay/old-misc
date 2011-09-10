<?php

/**
 * Class to filter mail sent via PHP's mail() command. You must create a PHP shell script (see below) and
 * change php.ini's sendmail_path to point to it. SendmailFilter will capture the raw message passed to it by
 * mail(), have it analyzed by Zend_Mail_Message_File and let you know if the message has only recipients in
 * your whitelist. If you wish, you may call passToSendmail(), which will pipe the raw message on to sendmail.
 *
 * Usage:
 * <code>

#!/usr/bin/php
<?php
// shell script pointed to by PHP's sendmail_path

// setup autoloading

// whitelist of recipient addresses.
$whitelist = array(...);

$filter = new MrClay_SendmailFilter();

if ($filter->allRecipientsValid($whitelist)) {
    $filter->passToSendmail();
} else {
    // e.g. dump in "blocked" directory
    $outfile = __DIR__ . '/blocked/' . uniqid(time() . '_') . '.txt';
    file_put_contents($outfile, $filter->rawMessage);
    chmod($outfile, 0666);
}

 * </code>
 * 
 */
class MrClay_SendmailFilter {
    
    public $sendmail_path = '/usr/sbin/sendmail -t -i';
    
    public $rawMessage = '';
    
    public function __construct()
    {
        if (defined('STDIN')) {
            $this->rawMessage = stream_get_contents(STDIN);
        }
    }
    
    /**
     * @return bool
     */
    public function passToSendmail()
    {
        if ($this->rawMessage) {
            $handle = popen($this->sendmail_path, "w");
            fwrite($handle, $this->rawMessage);
            pclose($handle);
            return true;
        }
        return false;
    }
    
    /**
     * @throws Exception
     * @param array $whitelist
     * @return bool
     */
    public function allRecipientsValid(array $whitelist)
    {
        if (! $this->rawMessage) {
            throw new Exception(get_class($this) . ': rawMessage is not set.'); 
        }
        $whitelist = (array) $whitelist;
        $fp = fopen('data:text/plain,' . urlencode($this->rawMessage), 'rb');
        $msg = new Zend_Mail_Message_File(array('file' => $fp));
        $headers = $msg->getHeaders();
        fclose($fp);
        
        // we're only going to keep whitelisted addresses here
        $newHeaders = array();

        foreach (array('to', 'cc', 'bcc') as $key) {
            if (! isset($headers[$key])) {
                continue;
            }
            $lines = (array) $headers[$key];
            $addrs = array();
            foreach ($lines as $line) {
                $addresses = explode(',', $line);
                foreach ($addresses as $fullAddress) {
                    $fullAddress = trim($fullAddress);
                    $email = $fullAddress;
                    if (false !== strpos($fullAddress, '<')) {
                        list(,$email) = preg_split('/[<>]/', $fullAddress);
                    }
                    if (in_array(strtolower($email), $whitelist)) {
                        $addrs[] = $fullAddress; 
                    } else {
                        return false;
                    }
                }
            }
            if ($addrs) {
                $newHeaders[$key] = $addrs;
            }
        }
        
        return true;
        /*
        // @todo add way to rewrite message
        
        // move at least one address into TO
        if (! isset($newHeaders['to'])) {
            if (isset($newHeaders['cc'])) {
                $newHeaders['to'][] = array_shift($newHeaders['cc']);
                if (! $newHeaders['cc']) {
                    unset($newHeaders['cc']);
                }
            } else if (isset($newHeaders['bcc'])) {
                $newHeaders['to'][] = array_shift($newHeaders['bcc']);
                if (! $newHeaders['bcc']) {
                    unset($newHeaders['bcc']);
                }
            }
        }*/
    }
}