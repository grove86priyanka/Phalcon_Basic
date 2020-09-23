<?php

/**
 * Mail library
 * @author amit
 */

namespace App\Library;

use Phalcon\Mvc\User\Component;

require_once __DIR__ . '/swift/swift_required.php';

class Mail extends Component {

    private $smtpHost = '';
    private $smtpPort = 0;
    private $smtpSecurity = 'tls';
    private $smtpUsername = '';
    private $smtpPassword = '';
    private $isSmtp = TRUE;
    private $from = array();
    private $to = array();
    private $cc = array();
    private $bcc = array();
    private $replyTo = array();
    private $subject = null;
    private $body = null;
    private $attachments = [];
    private $message = null;
    private $connection = null;
    private $mailer = null;

    private $testingEmail = null;
    static $testing = null;
    /**
     * SMTP Connection
     */
    private function connect() {
        if (!$this->connection) {
            if ($this->isSmtp()) {
                $this->smtpHost = $this->config->email->smtpHost;
                $this->smtpPort = $this->config->email->smtpPort;
                $this->smtpSecurity = $this->config->email->smtpSecurity;
                $this->smtpUsername = $this->config->email->smtpUsername;
                $this->smtpPassword = $this->config->email->smtpPassword;
                $this->connection = \Swift_SmtpTransport::newInstance($this->smtpHost, $this->smtpPort, $this->smtpSecurity)
                        ->setUsername($this->smtpUsername)
                        ->setPassword($this->smtpPassword);
            } else {
                $this->connection = \Swift_MailTransport::newInstance();
            }
        }
    }

    /**
     * Use SMTP or not
     * @return bool
     */
    public function isSmtp() {
        return $this->isSmtp;
    }

    /**
     * Change SMTP behaviour
     * @param bool $smtp
     */
    public function setIsSmtp($smtp) {
        $this->isSmtp = $smtp;
    }

    /**
     * Set To Address
     * @param string $address
     * @param string $name optional
     */
    public function setTo($address, $name = NULL) {
        if ($name == NULL) {
            $this->to[] = $address;
        } else {
            $this->to[$address] = $name;
        }
    }

    /**
     * Get To Address
     * @return array
     */
    public function getTo() {
        return $this->to;
    }

    /**
     * Setting From Address
     * @param string $address
     * @param string $name optional
     */
    public function setFrom($address, $name = NULL) {
        if ($name == NULL) {
            $this->from[] = $address;
        } else {
            $this->from[$address] = $name;
        }
    }

    /**
     * Get From Address
     * @return array
     */
    public function getFrom() {
        if (count($this->from) <= 0) {
            $this->from['no-reply@grove86.com'] = 'Grove86';
        }
        return $this->from;
    }

    /**
     * Set CC address
     * @param string $address
     * @param string $name optional
     */
    public function setCc($address, $name = NULL) {
        if ($name == NULL) {
            $this->cc[] = $address;
        } else {
            $this->cc[$address] = $name;
        }
    }

    /**
     * Get CC address
     * @return array
     */
    public function getCc() {
        return $this->cc;
    }

    /**
     * Set BCC address
     * @param string $address
     * @param string $name optional
     */
    public function setBcc($address, $name = NULL) {
        if ($name == NULL) {
            $this->bcc[] = $address;
        } else {
            $this->bcc[$address] = $name;
        }
    }

    /**
     * Get BCC address
     * @return array
     */
    public function getBcc() {
        return $this->bcc;
    }

    /**
     * Set Reply-To address
     * @param string $address
     * @param string $name optional
     */
    public function setReplyTo($address, $name = NULL) {
        if ($name == NULL) {
            $this->replyTo[] = $address;
        } else {
            $this->replyTo[$address] = $name;
        }
    }

    /**
     * Getting Reply-To address
     * @return array
     */
    public function getReplyTo() {
        return $this->replyTo;
    }

    /**
     * Setting Mail Subject
     * @param string $subject
     */
    public function setSubject($subject) {
        $this->subject = $subject;
    }

    /**
     * Get Mail Subject
     * @return string
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * Set Mail Body
     * @param mix $body
     */
    public function setBody($body) {
        $this->body = $body;
    }

    /**
     * Get Mail Body
     * @return string
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Add Mail Attachment
     * @param string $fileName
     */
    public function setAttachment($filePath, $fileName = "") { 
        $attachment = []; 
        $attachment['path'] = $filePath; 
        if ($fileName && $fileName!="") { 
            $attachment['name'] = $fileName; 
        } 
        $this->attachments[] = $attachment; 
    }

    /**
     * testing only for this instance
     * 
     * @param boolean|string $setting true: use email on file instead of To email, false: disable testing (even if it was enabled globaly/config), null: use global/config settings
     */
    public function setTesting($setting = true)
    {
        $return = $this->testingEmail;
        
        if ($setting) {
            if (strpos($setting, '@')) $this->testingEmail = $setting;
            else $this->testingEmail = true;
        } else $this->testingEmail = $setting;
        
        return $return;
    }

    /**
     * set testing for the remaining emails until you unset it or until page load finished
     * 
     * @param boolean|string $setting true: use email on file instead of To email, false: disable testing (even if it was enabled config), null: use config setting
     */
    public static function setTestingGlobal($setting = true)
    {
        $return = static::$testing;
        
        if ($setting) {
            if (strpos($setting, '@')) static::$testing = $setting;
            else static::$testing = true;
        } else static::$testing = $setting;
        
        return $return;
    }
    
    public static function getTestingGlobal()
    {
        return static::$testing;
    }
    
    public function getTesting()
    {
        $testingEmail = $this->testingEmail;
        if ($testingEmail === null) $testingEmail = static::$testing;
        if ($testingEmail === null) $testingEmail = $this->config->email->get('testing', false);

        if ($testingEmail) {
            if ($testingEmail && strpos($testingEmail, '@')) return $testingEmail;
            else return $this->config->email->get('testing_email', $this->config->common->get('admin_email'));
        } else return false;
    }


    /**
     * Building Mail with from, to, subject, body and attachment
     */
    private function _buildMessage() {
        $this->message = \Swift_Message::newInstance();
        if (count($this->getFrom()) > 0) {
            $this->message->setFrom($this->getFrom());
        }
        $to = $this->getTo();
        $testing = $this->getTesting();
        $prefixSubject = $prefixBody = '';

        if ($testing) {
            $this->message->setTo([$testing]);
            
            $prefixBody = [];
            if ($to) foreach ($to as $t_i => $t) $prefixBody[] = "To: " . (strpos($t_i, '@') ? (trim($t) ? "<$t> " : '') . "$t_i" : $t);
            if ($cc = $this->getCc()) foreach ($cc as $t_i => $t) $prefixBody[] = "CC: " . (strpos($t_i, '@') ? (trim($t) ? "<$t> " : '') . "$t_i" : $t);
            if ($bcc = $this->getBcc()) foreach ($bcc as $t_i => $t) $prefixBody[] = "BCC: " . (strpos($t_i, '@') ? (trim($t) ? "<$t> " : '') . "$t_i" : $t);
            
            $prefixSubject = '[TEST] ';
            $prefixBody = implode("<br>\r\n", $prefixBody) . "<br>\r\n<br>\r\n";
        } else {
            $this->message->setTo($to);

            if (count($this->getCc()) > 0) {
                $this->message->setCc($this->getCc());
            }
            if (count($this->getBcc()) > 0) {
                $this->message->setBcc($this->getBcc());
            }
        }
        if (count($this->getReplyTo()) > 0) {
            $this->message->setReplyTo($this->getReplyTo());
        }
        $this->message->setSubject($prefixSubject . $this->getSubject());
        $this->message->setBody($prefixBody . $this->getBody());
        $this->message->setContentType('text/html');
        if (count($this->attachments) > 0) {
            foreach ($this->attachments as $attachment) { 
                $attachObj = \Swift_Attachment::fromPath($attachment['path']); 
                if (isset($attachment['name']) && $attachment['name']) {
                    $attachObj->setFilename($attachment['name']); 
                } 
                $attachObj->setDisposition('inline'); 
                $this->message->attach($attachObj); 
            }
        }
    }

    /**
     * Sending Mail
     * @return bool
     */
    public function send() {
        $this->_buildMessage();
        $this->connect();
        $this->mailer = \Swift_Mailer::newInstance($this->connection);
        if (count($this->getTo()) > 0) {
            $this->mailer->send($this->message);
        }
        return true;
    }

}
