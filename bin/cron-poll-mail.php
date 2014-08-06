#!/usr/bin/php
<?php
/**
    @file
    @brief Cron POP3 Pickup

    Imports Messages from POP3/IMAP Mailbox to Request Queue
    Imports and Adds Attachments too
*/

/**
  Uses the Native PHP to read a IMAP/POP3(+SSL) mailbox and put messages into Imperium

  @todo drop dependency on ZF, use base-PHP
  @todo import to Trac using the Trac Web Interface - basically HTTP POST

*/

// CLI
require_once(dirname(dirname(__FILE__)) . '/lib/cli.php');

if (empty($_ENV['mail']['imap'])) {
    die("No IMAP Configuration\n");
}
echo "Checking: {$_ENV['mail']['imap']}\n";
$imap = new IMAP($_ENV['mail']['imap']);
$imap_stat = $imap->pathStat();
if (empty($imap_stat)) {
    die("Cannot Check Mailbox\n");
}
// print_r($imap_stat);

if ($imap_stat['mail_count'] == 0) {
    return(0);
}

for ($i=$imap_stat['mail_count'];$i>=1;$i--) {

    $stat = $imap->mailStat($i);

    // echo "S: {$stat['subject']} {$stat['MailDate']}\n";
    // print_r($stat['from'][0]);
    $from = $stat['from'][0];

    $from_mail = $from->mailbox . '@' . $from->host;
    if (!preg_match('/[\w\+\.]+@[\w\.]+/',$from_mail)) {
        die("Invalid From: " . print_r($from,true));
    }

    $sql = 'SELECT contact.* FROM contact LEFT JOIN contact_channel ON contact.id = contact_channel.contact_id ';
    $sql.= ' WHERE contact.email = ? OR contact_channel.data ilike ? ';
    $arg = array($from_mail,$from_mail);
    $res = radix_db_sql::fetchAll($sql,$arg);

    if ( (empty($res)) || (count($res) == 0) ) {
        echo "Skip: Unknown Sender: $from_mail\n";
        continue;
    }
    if (count($res) != 1) {
        echo "Skip: Multiple Matches: $from_mail\n";
        continue;
    }
    $c = $res[0];

    echo "From Contact: {$c['email']}\n";
    
    continue;

    // Examine Mail Parts
    // $part_list = mail_part_list($stat);
    // print_r($part_list);
    // continue;

    $wo = new WorkOrder(null);
    $wo['auth_user_id'] = 1;
    $wo['contact_id'] = $c['id'];
    $wo['requester'] = trim($from->personal);
    $wo['note'] = $stat['subject'] . "\n";

    $part_list = mail_part_list($stat);
    if ($part_list) {
        foreach ($part_list as $mime_id => $part) {
            echo "Mime Part: $mime_id\n";
            switch ($part['mime-type']) {
            case 'text/html':
                // @todo Attach to WO?
                echo "Unhandled HTML Part\n";
                break;
            case 'text/plain':
                switch ($part['mime-encoding']) {
                case '7bit':
                    $wo->note.= $imap->mailGet($i,$mime_id);
                    break;
                case 'quoted-printable':
                    $x = $imap->mailGet($i,$mime_id);
                    $wo->note.= quoted_printable_decode($x);
                    break;
                default:
                    die("Unknown Encoding: {$part['mime-encoding']}\n");
                }
                break;
            default:
                die("Unknown Type: {$part['mime-type']}\n");
            }
        }
    } else {
        // Just a Single Text Part
        $wo['note'] = $imap->mailGet($i);
    }
    $wo->save();

    echo "WO: #{$wo['id']} {$wo['requester']}\n"; //  {$wo->note}\n";

    $imap->mailWipe($i);

}
$imap->mailFlush();
exit;

function mail_part_list($stat,$depth=null)
{
    if ($depth === null) {
        $depth = null;
    }
    $count = 1;
    // echo "Depth: $depth\n";
    if (empty($stat['parts'])) {
        return false;
    }
    // print_r($stat['parts']);
    // exit;

    $list = array();
    foreach ($stat['parts'] as $i=>$chk) {
        // echo "Type: {$chk->type}\n";
        switch ($chk->type) {
        case TYPETEXT: // Text (0)
            switch (strtolower($chk->subtype)) {
            case 'html':
                $list[ "$depth$count" ] = array('mime-type' => 'text/html');
                break;
            case 'plain':
                $list[ "$depth$count" ] = array('mime-type' => 'text/plain');
                // Radix::dump($chk,true);
                break;
            default:
                die('Unknonw SubType');
            }
            // Encoding?
            switch ($chk->encoding) {
            case ENC7BIT:
                $list[ "$depth$count" ]['mime-encoding'] = '7bit';
                break;
            case ENC8BIT:
                die("8bit\n");
            case ENCBINARY:
                die("Binary\n");
            case ENCBASE64:
                die("Base64");
            case ENCQUOTEDPRINTABLE:
            case 4: // quoted-printable
                $list[ "$depth$count" ]['mime-encoding'] = 'quoted-printable';
                break;
            case ENCOTHER:
                die('Other');
            default:
                print_r($stat['head']);
                print_r($chk);
                die('Unknown Encoding: ' . $chk->encoding);
            }
            
            break;
        case TYPEMULTIPART: // Multipart?
            switch (strtolower($chk->subtype)) {
            case 'alternative':
                // Radix::dump($chk,true);
                $list[ "1" ] = array('mime-type' => 'multipart/alternative');
                $list += mail_part_list((array)$chk, "$count." );
                break;
            default:
                Radix::dump($chk);
                die("Unknown Multipart?\n");
            }
            break;
        case TYPEIMAGE: // Image, blindly accept
            $list[ "$depth$count" ] = array('mime-type' => 'image/' . strtolower($chk->subtype));
            break;
        default:
            Radix::dump($chk);
            die("Unknown Type: $chk->type\n");
        }
        $count++;
    }
    return $list;
}

// *******************************************************************************
// NOT USED
/*
if ( (SELF_AUTO_REPLY) || (preg_match('/^reply requested/im',$mail)) ) {
// $body = "Your request has been added $x\n";
$body = 'Content-Transfer-Encoding: 7bit' . SMTP_CRLF;
$body.= 'Content-Type: text/plain; charset=US-ASCII; format=flowed' . SMTP_CRLF;
$body.= 'Message-Id: <' . sha1(serialize($head).$mail) . '@' . SMTP_HELO . '>' . SMTP_CRLF;
$body.= 'Mime-Version: 1.0' . SMTP_CRLF;
$body.= 'Subject: New Ticket Request #' . $t['id'] . SMTP_CRLF;
$body.= 'To: ' . $t['reporter'] . SMTP_CRLF;
$body.= 'X-Mailer: Edoceo mail2trac' . SMTP_CRLF;
$body.= SMTP_CRLF;
$body.= 'Your request:' . SMTP_CRLF;
$body.= '  ' . $t['summary'] . SMTP_CRLF;
$body.= 'Has been received and acknowledged by Edoceo' . SMTP_CRLF;
$body.= SMTP_CRLF;
$body.= 'This is an automated response' . SMTP_CRLF;

$smtp->rset();
$smtp->mailFrom(SMTP_FROM);
$smtp->rcptTo($t['reporter']);
$smtp->data($body);
}
*/

/**
    IMAP Class Used by this Mail Poll
*/
class IMAP
{
    private $_c; // Connection Handle
    private $_c_host; // Server Part {}
    private $_c_base; // Base Path Requested
    /**
        Connect to an IMAP
    */
    function __construct($uri)
    {
        if (is_string($uri)) $uri = parse_url($uri);
        $this->_c = null;
        $this->_c_host = sprintf('{%s',$uri['host']);
        if (!empty($uri['port'])) {
            $this->_c_host.= sprintf(':%d',$uri['port']);
        }
        switch (strtolower($uri['scheme'])) {
        case 'ssl':
            $this->_c_host.= '/ssl';
            break;
        case 'tls':
            $this->_c_host.= '/tls';
            break;
        default:
        }
        $this->_c_host.= '}';

        $this->_c_base = $this->_c_host;
        // Append Path?
        if (!empty($uri['path'])) {
            $x = ltrim($uri['path'],'/');
            if (!empty($x)) {
                $this->_c_base = $x;
            }
        }
        // echo "imap_open($this->_c_host)\n";
        $this->_c = imap_open($this->_c_host,$uri['user'],$uri['pass']);
        if (empty($this->_c)) {
        	throw new Exception(implode(', ',imap_errors()));
        }
        // echo implode(', ',imap_errors());
    }

    /**
        List folders matching pattern
        @param $pat * == all folders, % == folders at current level
    */
    function listPath($pat='*')
    {
        $ret = array();
        $list = imap_getmailboxes($this->_c, $this->_c_host,$pat);
        foreach ($list as $x) {
            $ret[] = array(
                'name' => $x->name,
                'attribute' => $x->attributes,
                'delimiter' => $x->delimiter,
            );
        }
        return $ret;
    }

    /**
        Get a Message
    */
    function mailGet($i,$part='1')
    {
        // return imap_body($this->_c,$i,FT_PEEK);
        // return imap_savebody($this->_c,'mail',$i,null,FT_PEEK);
        return imap_fetchbody($this->_c,$i,$part);
    }

    /**
        Store a Message with proper date
    */
    function mailPut($mail,$opts,$date)
    {
        $stat = $this->pathStat();
        // print_r($stat);
        // $opts = '\\Draft'; // And Others?
        // $opts = null;
        // exit;
        $ret = imap_append($this->_c,$stat['check_path'],$mail,$opts,$date);
        print_r(imap_errors());
        return $ret;

    }

    /**
        Message Info
    */
    function mailStat($i)
    {
        $head = imap_headerinfo($this->_c,$i);
        $head = (array)$head;

        $rbuf = imap_fetchheader($this->_c,$i);
        $head['head'] = $rbuf;

        $info = imap_fetchstructure($this->_c,$i);
        if ($info) {
            $head = array_merge((array)$info,$head);
        }

        $stat = imap_fetch_overview($this->_c,$i);
        if (!empty($stat) && count($stat)==1 ) {
            $stat = (array)$stat[0];
            $head = array_merge($stat,$head);
        }
        return $head;
    }

    /**
        Immediately Delete and Expunge the message
    */
    function mailWipe($i)
    {
        imap_delete($this->_c,$i);
        // return imap_expunge($this->_c);
    }
    function mailFlush()
    {
        return imap_expunge($this->_c);
    }

    /**
        Sets the Current Mailfolder, Creates if Needed
    */
    function setPath($p,$make=false)
    {
        // echo "setPath($p);\n";
        if (substr($p,0,1)!='{') {
            $p = $this->_c_host . trim($p,'/');
        }
        // echo "setPath($p);\n";

        $ret = imap_reopen($this->_c,$p);
        // print_r($ret);
        print_r(imap_errors());

        // $ret = imap_createmailbox($this->_c,$p);
        // // print_r($ret);
        // print_r(imap_errors());

        return $ret;
    }

    /**
        Returns Information about the current Path
    */
    function pathStat()
    {
        $res = imap_mailboxmsginfo($this->_c);
        $ret = array(
            'date' => $res->Date,
            'path' => $res->Mailbox,
            'mail_count' => $res->Nmsgs,
            'size' => $res->Size,
        );
        $res = imap_check($this->_c);
        $ret['check_date'] = $res->Date;
        $ret['check_mail_count'] = $res->Nmsgs;
        $ret['check_path'] = $res->Mailbox;
        // $ret = array_merge($ret,$res);
        return $ret;
    }
}