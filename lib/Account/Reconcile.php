<?php
/**
    @file
    @brief Account Reconciliation Tools
    @version $Id$
    :mode=php:tabSize=4:

*/

class Account_Reconcile
{
    public static $format_list = array(
        'csvwfb' => 'Wells Fargo Comma Seperated',
        'square' => 'SquareUp Transaction CSV',
        'paypal' => 'Paypal CSV',
        'iq2005' => 'Intuit Quicken 2005 or newer',
        'qb2000' => 'Quickbooks 2000 or newer',
        'mm2002' => 'Microsoft Money 2002 or newer',
    );

    /**
    */
    static function parse($opt)
    {

        $ret = array();

        // Read the Line in the Format
        switch ($opt['kind']) {
        case 'csvwfb': // Wells Fargo CSV Format
            $ret = self::_parseWellsFargo($opt['file']);
            break;
        case 'paypal';
            // 0  = Date
            // 4  = Note
            // 6  = Gross
            // 7  = Fee
            // 8  = Net
            // 11 = Transaction ID
            // Zend_Debug::dump($_FILES['file']);
            $fh = fopen($_FILES['file']['tmp_name'],'r');
            while ($csv = fgetcsv($fh,4096)) {

                // Ledger Entry for Paypal Deposit (Gross)
                if (count($csv) < 11) {
                    continue;
                }
                // Skip first Row if Header
                if ($csv[0] == 'Date') {
                    continue;
                }
                // Only Process Completed Transactions
                if ($csv[5] == 'Pending') {
                    continue;
                }
                // Only Transactions with Fees Count
                if ( (empty($csv[7])) && (empty($csv[8])) ) {
                    continue;
                }

                $le = new stdClass();
                $le->date = $csv[0];
                $le->note = $csv[4] . ' #' . $csv[11];
                $le->account_id = null;
                switch (trim($csv[4])) {
                case 'Payment Received':
                case 'eBay Payment Received':
                case 'Shopping Cart Payment Received':
                    // Ledger Entry for Paypal Fee
                    $le->account_id = 111;
                    $le->note = 'Fee for Transaction #' . $csv[11] . '';
                    $le->amount = $le->dr = floatval(preg_replace('/[^\d\.\-]/',null,$csv[7]));
                    $this->view->JournalEntryList[] = $le;
                    // Ledger Entry for Paypal Deposit
                    $le = new stdClass();
                    $le->date = $csv[0];
                    $le->account_id = 8;
                    $le->note = $csv[4] . ' #' . $csv[11];
                    $le->amount = $le->cr = floatval(preg_replace('/[^\d\.\-]/',null,$csv[6]));
                    break;
                // Money Leaves PayPal to Expense
                case 'Payment Sent':
                case 'Express Checkout Payment Sent':
                case 'Shopping Cart Payment Sent':
                case 'Web Accept Payment Sent':
                case 'eBay Payment Sent':
                    // Debit to Checking
                    $le->amount = floatval(preg_replace('/[^\d\.\-]/',null,$csv[6]));
                    //if (floatval($le->amount) < 0) {
                    $le->dr = abs($le->amount);
                    $le->account_id = 26;
                    break;
                case 'Add Funds from a Bank Account': // Happens before Update to ...
                case 'Order': // Requested Money From Us, Paid on *Sent
                case 'Pending Balance Payment':
                    continue 2; // Ignore
                    break;
                case 'Refund':
                    // Debit to Checking
                    $le->amount = floatval(preg_replace('/[^\d\.\-]/',null,$csv[6]));
                    $le->cr = abs($le->amount);
                    $le->account_id = 26;
                    break;
                case 'Update to Add Funds from a Bank Account': // Money Into Paypal from Bank
                    // Debit to Checking
                    $le->amount = floatval(preg_replace('/[^\d\.\-]/',null,$csv[6]));
                    $le->cr = abs($le->amount);
                    $le->account_id = 1;
                    break;
                case 'Withdraw Funds to a Bank Account':
                    // Debit to Checking
                    $le->amount = floatval(preg_replace('/[^\d\.\-]/',null,$csv[6]));
                    //if (floatval($le->amount) < 0) {
                    $le->dr = abs($le->amount);
                    $le->account_id = 1;
                    break;
                default:
                    die($csv[4]);
                }
                $this->view->JournalEntryList[] = $le;
            }
            //Zend_Debug::dump($this->view->JournalEntryList);
            //exit(0);
            break;
        case 'qfx': // Quicken 2004 Web Connect
            //echo "<pre>".htmlspecialchars($buf)."</pre>";
            if (!preg_match('/^OFXHEADER:100/',$bf->data)) {
                trigger_error('Not a valid QFX file',E_USER_ERROR);
            }
              if (preg_match_all("/^<STMTTRN>\n<TRNTYPE>(CHECK|CREDIT|DEBIT|DEP|DIRECTDEBIT|FEE|POS)\n<DTPOSTED>(\d{8})\n<TRNAMT>([\d\-\.]+)\n<FITID>(\d+)\n<NAME>(.+)<\/STMTTRN>\n/m",$bf->data,$m)) {
                $c_entries = count($m[0]);
                $trn_types = $m[1];
                $trn_dates = $m[2];
                $trn_amnts = $m[3];
                $trn_fitid = $m[4];
                $trn_names = $m[5];
                // echo "<pre>".print_r($trn_names,true)."</pre>";
                for ($i=0;$i<$c_entries;$i++)
                {
                  $je = new stdClass();
                  $je->id = null;
                  $je->ok = false;
                  $je->index = $i;
                  $je->date = substr($trn_dates[$i],4,2).'/'.substr($trn_dates[$i],6,2).'/'.substr($trn_dates[$i],0,4);
                  $je->amount = $trn_amnts[$i];
                  $je->note = $trn_names[$i];
                  $je->offset_account_id = null;
                  $this->view->JournalEntryList[] = $je;
                }
            }
        case 'square':
            $ret = self::_parseSquare($opt['file']);
            break;
        }

        // Now Spin Each List Item and Discover Existing Journal Entry?
        $c = count($ret);
        for ($i=0;$i<$c;$i++) {

        	// Old
            // $s = $d->select();
            // $s->from('general_ledger',array('account_journal_id','date','amount'));
            // $s->where(" (date <= ?::timestamp + '5 days'::interval) AND (date >= ? ::timestamp - '5 days'::interval) ",$ret[$i]->date);
            // $s->where(' account_id = ?',$opt['account_id']);
            // $s->where(' abs(amount) = ?',abs($ret[$i]->abs));
            // $ret[$i]->id = $d->fetchOne($s);

            // New
            $sql = 'SELECT account_journal_id, date, amount FROM general_ledger';
            $sql.= " WHERE (date <= ?::timestamp + '5 days'::interval) AND (date >= ? ::timestamp - '5 days'::interval)";
            $sql.= ' AND account_id = ?';
            $sql.= ' AND abs(amount) = ?';
            $ret[$i]->id = SQL::fetch_one($sql, array($ret[$i]->date, $ret[$i]->date, $opt['account_id'], abs($ret[$i]->abs)));

            // $sql = 'select a.id,a.date,b.amount';
            // $sql.= ' from account_journal a join account_ledger b on a.id=b.account_journal_id ';
            // $sql.= ' where ';
            // $sql.= " (date<='{$je->date}'::timestamp+'5 days'::interval and date>='{$je->date}'::timestamp-'5 days'::interval) ";
            // $sql.= " and abs(b.amount)='{$je->abs}' ";
            // $sql.= ' b.account_id=' . $acct_id and abs(b.amount)='".abs($entry->amount)."' ";
            // echo "$sql\n";
            // echo $s->assemble();

        }

        uasort($ret,array(self,'_sortCallback'));

        return $ret;
    }

    /**
        Parse WellsFargo CSV to Journal Entry Array
    */
    private static function _parseWellsFargo($file)
    {
        $ret = array();
        $fh = fopen($file,'r');
        while ($csv = fgetcsv($fh,4096)) {
            $je = new stdClass();
            if (count($csv) < 4) {
                continue;
            }
            $je->date = $csv[0];
            $je->note = $csv[4];
            $je->abs = abs(preg_replace('/[^\d\.]+/',null,$csv[1]));
            if ($csv[1] < 0) {
                $je->cr = abs($csv[1]);
            } else {
                $je->dr = abs($csv[1]);
            }

            // Apply Filter Here?
            $je = self::_filterEntry($je);
            $je = self::_guessAccount($je);

            $ret[] = $je;
        }
        return $ret;
    }
    
    /**
        Parse Square.com Transactions
        @todo Need to Make TWO entries
              One to Square for the Full Amount #5
              One to Payment Processors for Fee 
    */
    private static function _parseSquare($file)
    {
        $ret = array();
        $fh = fopen($file,'r');
        while ($csv = fgetcsv($fh,4096)) {
            $x = strtolower(trim($csv[0]));
            if ($x == 'date') continue;
            if (count($csv) < 4) continue;

            $je = new stdClass();
            $je->date = strftime('%Y-%m-%d %H:%M:%S',strtotime($csv[0]));
            $je->note = $csv[21] . '#' . $csv[22] . ' ' . $csv[26];
            $je->ledger = array();
            
            // Transaction Amount
            $le = array();
            $x = floatval(preg_replace('/[^\d\.]+/',null,$csv[19])); // Is it 13 or 19?
            if ($x < 0) {
                $le['cr'] = abs($x);
            } else {
                $le['dr'] = abs($x);
            }
            $je->ledger[] = $le;

            // Apply Filter Here?
            // $je = self::_filterEntry($je);
            // $je = self::_guessAccount($je);

            // The Fee Entry
            // $fee = floatval(preg_replace('/[^\d\.]+/',null,$csv[13]));
            $fee = floatval(preg_replace('/[^\d\.]+/',null,$csv[18]));
            $je->ledger[] = array(
            	'note' => 'Fee for Transaction #' . $csv[20],
            	'abs' => abs($fee),
            	'cr' => $fee,
			);
            // = floatval(preg_replace('/[^\d\.]+/',null,$csv[13]));
            // $je->cr = floatval(preg_replace('/[^\d\.]+/',null,$csv[18]));
            // $je->abs = ($je->cr);
            
            $ret[] = $je;

        }
        return $ret;
    }

    /**
        Filter the Transactions?
    */
    private static function _filterEntry($je)
    {
        $je->note = str_replace('CHECK CRD PURCHASE ',null,$je->note);
        // $je->note = str_replace('CHECK CRD PURCHASE ',null,$je->note);
        return $je;
    }
    /**
        Guess the Opposition Account
    */
    private static function _guessAccount($je)
    {
        
        return $je;
    }
    /**
        Sorts Journal Entries
    */
    private static function _sortCallback($a,$b)
    {
        // Compare by Time (Lowest First)
        $x0 = strtotime($a->date);
        $x1 = strtotime($b->date);
        if ($x0 != $x1) {
            return ($x0 > $x1);
        }
        // Compare by Amount (Highest First)
        $x0 = floatval($a->abs);
        $x1 = floatval($b->abs);
        return ($x0 < $x1);
    }
}


