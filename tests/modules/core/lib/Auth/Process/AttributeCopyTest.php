<?php

use PHPUnit\Framework\TestCase;

/**
 * Test for the core:AttributeCopy filter.
 */
class Test_Core_Auth_Process_AttributeCopy extends TestCase
{

    /**
     * Helper function to run the filter with a given configuration.
     *
     * @param array $config  The filter configuration.
     * @param array $request  The request state.
     * @return array  The state array after processing.
     */
    private static function processFilter(array $config, array $request)
    {
        $filter = new \SimpleSAML\Module\core\Auth\Process\AttributeCopy($config, NULL);
        $filter->process($request);
        return $request;
    }

    /**
     * Test the most basic functionality.
     */
    public function testBasic()
    {
        $config = [
            'test' => 'testnew',
        ];
        $request = [
            'Attributes' => ['test' => ['AAP']],
        ];
        $result = self::processFilter($config, $request);
        $attributes = $result['Attributes'];
        $this->assertArrayHasKey('test', $attributes);
        $this->assertArrayHasKey('testnew', $attributes);
        $this->assertEquals($attributes['testnew'], ['AAP']);
    }

    /**
     * Test the most basic functionality.
     */
    public function testArray()
    {
        $config = [
            'test' => ['new1','new2'],
        ];
        $request = [
            'Attributes' => ['test' => ['AAP']],
        ];
        $result = self::processFilter($config, $request);
        $attributes = $result['Attributes'];
        $this->assertArrayHasKey('test', $attributes);
        $this->assertArrayHasKey('new1', $attributes);
        $this->assertArrayHasKey('new2', $attributes);
        $this->assertEquals($attributes['new1'], ['AAP']);
        $this->assertEquals($attributes['new2'], ['AAP']);
    }

    /**
     * Test that existing attributes are left unmodified.
     */
    public function testExistingNotModified()
    {
        $config = [
            'test' => 'testnew',
        ];
        $request = [
            'Attributes' => [
                'test' => ['AAP'],
                'original1' => ['original_value1'],
                'original2' => ['original_value2'],
            ],
        ];
        $result = self::processFilter($config, $request);
        $attributes = $result['Attributes'];
        $this->assertArrayHasKey('testnew', $attributes);
        $this->assertEquals($attributes['test'], ['AAP']);
        $this->assertArrayHasKey('original1', $attributes);
        $this->assertEquals($attributes['original1'], ['original_value1']);
        $this->assertArrayHasKey('original2', $attributes);
        $this->assertEquals($attributes['original2'], ['original_value2']);
    }

    /**
     * Test copying multiple attributes
     */
    public function testCopyMultiple()
    {
        $config = [
            'test1' => 'new1',
            'test2' => 'new2',
        ];
        $request = [
            'Attributes' => ['test1' => ['val1'], 'test2' => ['val2.1','val2.2']],
        ];
        $result = self::processFilter($config, $request);
        $attributes = $result['Attributes'];
        $this->assertArrayHasKey('new1', $attributes);
        $this->assertEquals($attributes['new1'], ['val1']);
        $this->assertArrayHasKey('new2', $attributes);
        $this->assertEquals($attributes['new2'], ['val2.1','val2.2']);
    }

    /**
     * Test behaviour when target attribute exists (should be replaced).
     */
    public function testCopyClash()
    {
        $config = [
            'test' => 'new1',
        ];
        $request = [
            'Attributes' => [
                'test' => ['testvalue1'],
                'new1' => ['newvalue1'],
            ],
        ];
        $result = self::processFilter($config, $request);
        $attributes = $result['Attributes'];
        $this->assertEquals($attributes['new1'], ['testvalue1']);
    }

    /**
     * Test wrong attribute name
     *
     * @expectedException Exception
     */
    public function testWrongAttributeName()
    {
        $config = [
            ['value2'],
        ];
        $request = [
            'Attributes' => [
                'test' => ['value1'],
            ],
        ];
        self::processFilter($config, $request);
    }

    /**
     * Test wrong attribute value
     *
     * @expectedException Exception
     */
    public function testWrongAttributeValue()
    {
        $config = [
            'test' => 100,
        ];
        $request = [
            'Attributes' => [
                'test' => ['value1'],
            ],
        ];
        self::processFilter($config, $request);
    }
}
