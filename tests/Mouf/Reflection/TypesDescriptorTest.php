<?php
namespace Mouf\Reflection;

use Mouf\MoufManager;
use Mouf\MoufInstanceDescriptor;

require_once __DIR__.'/../TestClasses/TestClassA.php';
require_once __DIR__.'/../TestClasses/TestSubClassA.php';

class TypesDescriptorTest extends \PHPUnit_Framework_TestCase
{

    public function testRunLexer()
    {
        $tokens = TypesDescriptor::runLexer("test<tata,titi>|toto[]");

        $this->assertEquals("T_TYPE", $tokens[0]['token']);
        $this->assertEquals("T_START_ARRAY", $tokens[1]['token']);
        $this->assertEquals("T_TYPE", $tokens[2]['token']);
        $this->assertEquals("T_COMA", $tokens[3]['token']);
        $this->assertEquals("T_TYPE", $tokens[4]['token']);
        $this->assertEquals("T_END_ARRAY", $tokens[5]['token']);
        $this->assertEquals("T_OR", $tokens[6]['token']);
        $this->assertEquals("T_TYPE", $tokens[7]['token']);
        $this->assertEquals("T_ARRAY", $tokens[8]['token']);
    }

    public function testConstructor()
    {
        $types = TypesDescriptor::parseTypeString("test<tata,titi>|toto[]|MyType");

        $typesList = $types->getTypes();

        $this->assertEquals(3, count($typesList));
        $this->assertEquals("test", $typesList[0]->getType());
        $this->assertEquals("tata", $typesList[0]->getKeyType());
        $this->assertNotNull($typesList[0]->getSubType());
        $this->assertEquals("titi", $typesList[0]->getSubType()->getType());
        $this->assertEquals("array", $typesList[1]->getType());
        $this->assertEquals("toto", $typesList[1]->getSubType()->getType());
        $this->assertEquals("MyType", $typesList[2]->getType());

        $types2 = TypesDescriptor::parseTypeString("array<string>");
        $typesList2 = $types2->getTypes();
        $this->assertEquals(1, count($typesList2));

        $types3 = TypesDescriptor::parseTypeString("string|array<string>");
        $typesList3 = $types3->getTypes();
        $this->assertEquals("array", $typesList3[1]->getType());
        $this->assertEquals(true, $typesList3[1]->isArray());
        $this->assertEquals(2, count($typesList3));

        $types4 = TypesDescriptor::parseTypeString("array<string,string>");
        $typesList4 = $types4->getTypes();
        $this->assertEquals("array", $typesList4[0]->getType());
        $this->assertEquals(true, $typesList4[0]->isArray());
        $this->assertEquals(true, $typesList4[0]->isAssociativeArray());
        $this->assertEquals("string", $typesList4[0]->getKeyType());
        $this->assertEquals("string", $typesList4[0]->getSubType()->getType());
        $this->assertEquals(1, count($typesList4));

        $types5 = TypesDescriptor::parseTypeString("array<string, string>");
        $typesList5 = $types5->getTypes();
        $this->assertEquals("array", $typesList5[0]->getType());
        $this->assertEquals(true, $typesList5[0]->isArray());
        $this->assertEquals(true, $typesList5[0]->isAssociativeArray());
        $this->assertEquals("string", $typesList5[0]->getKeyType());
        $this->assertEquals("string", $typesList5[0]->getSubType()->getType());
        $this->assertEquals(1, count($typesList5));

        $types6 = TypesDescriptor::parseTypeString("array<int, string[]>");
        $typesList6 = $types6->getTypes();
        $this->assertEquals("array", $typesList6[0]->getType());
        $this->assertEquals(true, $typesList6[0]->isArray());
        $this->assertEquals(true, $typesList6[0]->isAssociativeArray());
        $subType = $typesList6[0]->getSubType();
        $this->assertEquals("int", $typesList6[0]->getKeyType());
        $this->assertEquals(true, $subType->isArray());
        $this->assertEquals(false, $subType->isAssociativeArray());
        $this->assertEquals("string", $subType->getSubType()->getType());

        $types7 = TypesDescriptor::parseTypeString("array<int, array<string> >");
        $typesList7 = $types7->getTypes();
        $this->assertEquals("array", $typesList7[0]->getType());
        $this->assertEquals(true, $typesList7[0]->isArray());
        $this->assertEquals(true, $typesList7[0]->isAssociativeArray());
        $subType = $typesList7[0]->getSubType();
        $this->assertEquals("int", $typesList7[0]->getKeyType());
        $this->assertEquals(true, $subType->isArray());
        $this->assertEquals(false, $subType->isAssociativeArray());
        $this->assertEquals("string", $subType->getSubType()->getType());

        $types8 = TypesDescriptor::parseTypeString("array<int, array< array<string, string> > >");
        $typesList8 = $types8->getTypes();
        $this->assertEquals("array", $typesList8[0]->getType());
        $this->assertEquals(true, $typesList8[0]->isArray());
        $this->assertEquals(true, $typesList8[0]->isAssociativeArray());
        $subType = $typesList8[0]->getSubType();
        $this->assertEquals("int", $typesList8[0]->getKeyType());
        $this->assertEquals(true, $subType->isArray());
        $this->assertEquals(false, $subType->isAssociativeArray());
        $this->assertEquals(true, $subType->getSubType()->isArray());
    }

    public function testLocalCache()
    {
        $types = TypesDescriptor::parseTypeString("test<tata,titi>|toto[]|MyType");
        $types2 = TypesDescriptor::parseTypeString("test<tata,titi>|toto[]|MyType");
        $types3 = TypesDescriptor::parseTypeString("test<tata,titi>|toto[]|MyType2");

        $this->assertEquals($types, $types2);
        $this->assertNotEquals($types, $types3);
    }

    public function testGetCompatibleTypeForInstance()
    {
        // First testing type: "string"
        $types1 = TypesDescriptor::parseTypeString("string");
        $result = $types1->getCompatibleTypeForInstance("ma string");
        $this->assertEquals($result->getType(), "string");

        $result = $types1->getCompatibleTypeForInstance(new MoufInstanceDescriptor(new MoufManager(), "test"));
        $this->assertEquals(null, $result);

        $result = $types1->getCompatibleTypeForInstance(array("toto", "tata"));
        $this->assertEquals(null, $result);

        // Second testing type: "string|array<string>"
        $types2 = TypesDescriptor::parseTypeString("string|array<string>");

        $result = $types2->getCompatibleTypeForInstance("ma string");
        $this->assertEquals("string", $result->getType());

        $result = $types2->getCompatibleTypeForInstance(new MoufInstanceDescriptor(new MoufManager(), "test"));
        $this->assertEquals(null, $result);

        $result = $types2->getCompatibleTypeForInstance(array("toto", "tata"));
        $this->assertEquals("array", $result->getType());
        $this->assertEquals("string", $result->getSubType()->getType());

        // If null, we should select the first value
        $result = $types2->getCompatibleTypeForInstance(null);
        $this->assertEquals("string", $result->getType());

        // Third testing type: "Mouf\\TestClasses\\TestClassA"
        $types3 = TypesDescriptor::parseTypeString("Mouf\\TestClasses\\TestClassA");

        $result = $types3->getCompatibleTypeForInstance("ma string");
        $this->assertEquals(null, $result);

        /*$instanceDescriptorStub = $this->getMock('Mouf\\MoufInstanceDescriptor', array(), array(new MoufManager(), "name"));

        $instanceDescriptorStub->expects($this->any())
                                ->method('getClassName')
                                ->will($this->returnValue('Mouf\\TestClasses\\TestClassA'));


        $result = $types3->getCompatibleTypeForInstance($instanceDescriptorStub);
        $this->assertEquals('Mouf\\TestClasses\\TestClassA', $result->getType());

        // TODO: we should be able to test with a stub instance descriptor.
        */
    }

    public static function main()
    {
        $suite = new \PHPUnit_Framework_TestSuite(__CLASS__);
        \PHPUnit_TextUI_TestRunner::run($suite);
    }
}

if (!defined('PHPUnit_MAIN_METHOD')) {
    TypesDescriptorTest::main();
}
