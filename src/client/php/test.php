<?php
require_once 'arakoon.php';
require_once 'php_unit_test_framework/php_unit_test.php';
require_once 'php_unit_test_framework/text_test_runner.php';
require_once 'php_unit_test_framework/xhtml_test_runner.php';

/*
 * TODO: Run python script to construct the Arakoon server
 */

define("ARAKOON_CLUSTER", "sampleapp");

function GetArakoon()
{
    $clusterId = ARAKOON_CLUSTER;
    $nodes = array(
        'sampleapp_0' => array('ip'=>'127.0.0.1', 'port' => 20100)
    );

    $cfg = new ArakoonClientConfig ($clusterId, $nodes);
    $arakoon = new Arakoon($cfg);      
    return $arakoon;
}

/*
 * SET/GET
 */
class ArakoonSetGet extends TestCase
{
    private $arakoon=null;
    public function __construct()
    {
        parent::__construct('SET/Get','Testing Arakoon Set and Get', 'ARA_UT_1');
    }

    public function SetUp()
    {
        $this->arakoon = GetArakoon();
    }

    public function Run()
    {
        $key = 'key1'; 
        $value = 'value1';
        $this->arakoon->set($key, $value);
        $val = $this->arakoon->get($key);
        $this->AssertEquals($value, $val, 'SET/GET FAILED');
    }

  public function TearDown()
  {
      $this->AddMessage("Tear Down: ".$this->GetName(). "[" . $this->GetID() . "] test case!");
  }
}

/*
 * SEQUENCE/EXISTS
 */
class ArakoonSequenceExists extends TestCase
{
    private $arakoon=null;
    public function __construct()
    {
        parent::__construct('SEQUENCE/EXISTS','Testing Arakoon Sequence and Exists', 'ARA_UT_2');
    }

    public function SetUp()
    {
        $this->arakoon = GetArakoon();
    }

    public function Run()
    {
        $kseq1 = "KeySeq1"; $vseq1="ValueSeq1";
        $kseq2 = 'key1';

        $seq = new Sequence();
        $this->arakoon->set($kseq2, $kseq2);        
        $seq->addSet($kseq1, $vseq1);
        $seq->addDelete($kseq2);
        $this->arakoon->sequence($seq);
        $val = $this->arakoon->get($kseq1);
        $bool = $this->arakoon->exists($kseq2);
        
        $this->AssertEquals($vseq1, $val, 'Sequence Failed!');
        $this->AssertEquals($bool, 0, 'Exists Failed!');
    }

  public function TearDown()
  {
      $this->AddMessage("Tear Down: ".$this->GetName(). "[" . $this->GetID() . "] test case!");
  }
}


/*
 * RANGE
 */
class ArakoonRange extends TestCase
{
    private $arakoon=null;
    private $keys = array();
    private $values = array();
        
    public function __construct()
    {
        parent::__construct('RANGE','Testing Arakoon Range', 'ARA_UT_3');
    }

    public function SetUp()
    {
        $this->arakoon = GetArakoon();

        for ($i = 0; $i < 5; $i++)
        {
            $this->keys[] = "k$i";
            $this->values[] = "value$i";
            $this->arakoon->set($this->keys[$i], $this->values[$i]);
        }        
    }

    public function Run()
    {
        $rkeys = $this->arakoon->range($this->keys[0], TRUE, $this->keys[4], TRUE);
        $this->AssertNotEquals(count($rkeys), 0, 'Range Failed!');
    }

  public function TearDown()
  {
      $this->AddMessage("Tear Down: ".$this->GetName(). "[" . $this->GetID() . "] test case!");
  }
}


/*
 * RANGE ENTRIES
 */
class ArakoonRangeEntries extends TestCase
{
    private $arakoon=null;
    private $keys = array();
    private $values = array();
    
    public function __construct()
    {
        parent::__construct('RANGE ENTRIES','Testing Arakoon Range Entries', 'ARA_UT_4');
    }

    public function SetUp()
    {
        $this->arakoon = GetArakoon();
        
        for ($i = 0; $i < 5; $i++)
        {
            $this->keys[] = "k$i";
            $this->values[] = "value$i";
            $this->arakoon->set($this->keys[$i], $this->values[$i]);
        }
    }

    public function Run()
    {
        $rkeys = array();
        $rkeys = $this->arakoon->range_entries($this->keys[0], TRUE, $this->keys[4], TRUE);
        $this->AssertNotEquals(count($rkeys), 0, 'Range Entries Failed!');
    }

  public function TearDown()
  {
      $this->AddMessage("Tear Down: ".$this->GetName(). "[" . $this->GetID() . "] test case!");
  }
}


/*
 * TEST AND SET 
 */
class ArakoonTestAndSet extends TestCase
{
    private $arakoon=null;
    private $tskey = 'testandsetkey';
    private $oldvalue = 'oldvalue';
    private $newvalue = 'newvalue';
    
    public function __construct()
    {
        parent::__construct('TEST AND SET','Testing Arakoon TestAndSet', 'ARA_UT_5');
    }

    public function SetUp()
    {
        $this->arakoon = GetArakoon();
        
        $this->arakoon->set($this->tskey, $this->oldvalue);
    }

    public function Run()
    {
        $rvalue = $this->arakoon->testAndSet($this->tskey, $this->oldvalue, $this->newvalue);

        $this->AssertEquals($rvalue, $this->oldvalue, 'TestAndSet Failed!');
    }

  public function TearDown()
  {
      $this->AddMessage("Tear Down: ".$this->GetName(). "[" . $this->GetID() . "] test case!");
  }
}


/*
 * MULTI GET
 */
class ArakoonMultiGet extends TestCase
{
    private $arakoon=null;
    private $keys = array();
    private $values = array();
        
    public function __construct()
    {
        parent::__construct('MULTI GET','Testing Multi Get', 'ARA_UT_6');
    }

    public function SetUp()
    {
        $this->arakoon = GetArakoon();

        for ($i = 0; $i < 5; $i++)
        {
            $this->keys[] = "k$i";
            $this->values[] = "value$i";
            $this->arakoon->set($this->keys[$i], $this->values[$i]);
        }        
    }

    public function Run()
    {
        $vals = array();
        $vals = $this->arakoon->multiGet($this->keys);

        $this->AssertNotEquals(count($vals), 0, 'Multi Get Failed!');
        $i=0;
        foreach ($vals as $value) {
            $this->AssertEquals($value, $this->values[$i], 'Multi Get values Failed!');
            $i++;
        }
    }

  public function TearDown()
  {
      $this->AddMessage("Tear Down: ". $this->GetName() . "[" . $this->GetID() . "] test case!");
  }
}


/*
 * DELETE
 */
class ArakoonDelete extends TestCase
{
    private $arakoon=null;
    private $key = 'keyDel';
        
    public function __construct()
    {
        parent::__construct('DELETE','Testing Delete', 'ARA_UT_7');
    }

    public function SetUp()
    {
        $this->arakoon = GetArakoon();

        $this->arakoon->set($this->key, $this->key);
    }

    public function Run()
    {
        $this->arakoon->delete($this->key);
        $bool = $this->arakoon->exists($this->key);

        $this->AssertEquals($bool, 0, 'Delete Failed!');
    }

  public function TearDown()
  {
      $this->AddMessage("Tear Down: ". $this->GetName() . "[" . $this->GetID() . "] test case!");
  }
}


/*
 * PREFIX
 */
class ArakoonPrefix extends TestCase
{
    private $arakoon=null;
    private $keys = array();
        
    public function __construct()
    {
        parent::__construct('PREFIX','Testing Prefix', 'ARA_UT_8');
    }

    public function SetUp()
    {
        $this->arakoon = GetArakoon();

        for ($i = 0; $i < 5; $i++)
        {
            $this->keys[] = "k$i";
            $this->arakoon->set($this->keys[$i], $this->keys[$i]);
        }      
    }

    public function Run()
    {
        $prefix = 'k';
        $rkeys = array();
        $rkeys = $this->arakoon->prefix($prefix);

        $this->AssertNotEquals(count($rkeys) , 0, 'Prefix Failed!');
    }

  public function TearDown()
  {
      $this->AddMessage("Tear Down: ". $this->GetName() . "[" . $this->GetID() . "] test case!");
  }
}

/*
 * EXPECT PROGRESS POSSIBLE
 */
class ArakoonExpectProgressPossible extends TestCase
{
    private $arakoon=null;
        
    public function __construct()
    {
        parent::__construct('EXPECT PROGRESS POSSIBLE','Testing Expect Progress Possible', 'ARA_UT_9');
    }

    public function SetUp()
    {
        $this->arakoon = GetArakoon();
    }

    public function Run()
    {
        $bool = $this->arakoon->expectProgressPossible();
        $this->AssertEquals($bool, 1, 'Expect Progress Possible Failed!');
    }

  public function TearDown()
  {
      $this->AddMessage("Tear Down: ". $this->GetName() . "[" . $this->GetID() . "] test case!");
  }
}

/*
 * HELLO / WHO-MASTER
 */
class ArakoonHelloWhoMaster extends TestCase
{
    private $arakoon=null;
        
    public function __construct()
    {
        parent::__construct('HELLO/MASTER','Testing Hello/Masetr', 'ARA_UT_10');
    }

    public function SetUp()
    {
        $this->arakoon = GetArakoon();
    }

    public function Run()
    {
        $clusterId = ARAKOON_CLUSTER;
        $hello = $this->arakoon->hello("clientId", $clusterId);
        $master = $this->arakoon->whoMaster();
        if(strlen($hello) == 0 || strlen($master) == 0){
            $ErrFunctions[] = array('result' => FALSE, 'name' => 'HELLO / WHO-MASTER');
        }else{
            $ErrFunctions[] = array('result' => TRUE, 'name' => 'HELLO / WHO-MASTER');
        }

        $this->AssertNotEquals(strlen($hello), 0, 'Hello Failed!');
        $this->AssertNotEquals(strlen($master), 0, 'Master Failed!');
        
        $this->AddMessage("HELLO, MASTER: $hello, $master");
    }

  public function TearDown()
  {
      $this->AddMessage("Tear Down: ". $this->GetName() . "[" . $this->GetID() . "] test case!");
  }
}

/*
 * Report
 */
$suite = new TestSuite();

$suite->AddTest('ArakoonSetGet');
$suite->AddTest('ArakoonSequenceExists');
$suite->AddTest('ArakoonRange');
$suite->AddTest('ArakoonRangeEntries');
$suite->AddTest('ArakoonTestAndSet');
$suite->AddTest('ArakoonMultiGet');
$suite->AddTest('ArakoonDelete');
$suite->AddTest('ArakoonPrefix');
$suite->AddTest('ArakoonExpectProgressPossible');
$suite->AddTest('ArakoonHelloWhoMaster');

$runner = new XMLTestRunner();
$runner->Run($suite, 'report');

?>