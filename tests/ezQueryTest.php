<?php

namespace ezsql\Tests;

use ezsql\ezQuery;
use ezsql\Tests\EZTestCase;

class ezQueryTest extends EZTestCase
{
    protected $object;

    protected function setUp(): void
    {
        $this->object = new ezQuery();
    }

    protected function tearDown(): void
    {
        $this->object = null;
    }

    public function testClean()
    {
        $this->assertEquals("' help", $this->object->clean("<?php echo 'foo' >' help</php?>"));
    }

    public function testHaving()
    {
        $this->assertFalse($this->object->having(''));
        $this->assertEmpty($this->object->having());

        $expect = $this->object->having(in('other_test', 'testing 1', 'testing 2', 'testing 3', 'testing 4', 'testing 5'));

        $this->assertContains('HAVING', $expect);
    }

    public function testWhere()
    {
        $this->assertFalse($this->object->where(''));
        $this->assertEmpty($this->object->where());

        $expect = $this->object->where(in('where_test', 'testing 1', 'testing 2', 'testing 3', 'testing 4', 'testing 5'));

        $this->assertContains('WHERE', $expect);
        $this->assertContains('IN', $expect);
        $this->assertContains('(', $expect);
        $this->assertContains('testing 2\'', $expect);
        $this->assertContains('testing 5', $expect);
        $this->assertContains(')', $expect);

        $this->assertContains(
            'AND',
            $this->object->where(
                array('where_test', '=', 'testing 1'),
                array('test_like', _LIKE, '_good')
            )
        );

        $this->object->prepareOn();
        $this->assertContains('__ez__', $this->object->where(eq('where_test', 'testing 1')));
        $this->assertFalse($this->object->where(like('where_test', 'fail')));
    }

    public function testPrepareOn()
    {
        $this->object->prepareOn();
        $expect = $this->object->where(
            ['where_test', _IN, 'testing 1', 'testing 2', 'testing 3', 'testing 4', 'testing 5']
        );

        $this->assertEquals(5, preg_match_all('/__ez__/', $expect));
    }

    public function testPrepareOff()
    {
        $this->object->prepareOff();

        $this->assertFalse(
            $this->object->where(
                array('where_test', '=', 'testing 1', 'or'),
                array('test_like', 'LIKE', ':bad')
            )
        );
    }

    public function testAddPrepare()
    {
        $this->object->prepareOn();
        $expect = $this->object->where(
            eq('where_test', 'testing 1'),
            neq('some_key', 'other', _OR),
            like('other_key', '%any')
        );

        $this->assertEquals(3, preg_match_all('/__ez__/', $expect));
    }

    public function testDelete()
    {
        $this->assertFalse($this->object->delete(''));
        $this->assertFalse($this->object->delete('test_unit_delete', array('good', 'bad')));
    }

    public function testSelecting()
    {
        $this->assertFalse($this->object->selecting('', ''));
        $this->assertNotNull($this->object->selecting('table', 'columns', 'WHERE', 'GROUP BY', 'HAVING', 'ORDER BY', 'LIMIT'));
    }

    public function testCreate_select()
    {
        $this->assertFalse($this->object->create_select('', '', ''));
    }

    public function testInsert_select()
    {
        $this->assertFalse($this->object->insert_select('', '', ''));
    }

    public function testInsert()
    {
        $this->assertFalse($this->object->insert('', ''));
    }

    public function testUpdate()
    {
        $this->assertFalse($this->object->update('', ''));
        $this->assertFalse($this->object->update('test_unit_delete', array('test_unit_update' => 'date()'), ''));
    }

    public function testReplace()
    {
        $this->assertFalse($this->object->replace('', ''));
    }

    public function test__Construct()
    {
        $ezQuery = $this->getMockBuilder(ezQuery::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertNull($ezQuery->__construct());
    }
}
