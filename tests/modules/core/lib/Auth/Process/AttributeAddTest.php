<?php

use PHPUnit\Framework\TestCase;

/**
 * Test for the core:AttributeAdd filter.
 */
class Test_Core_Auth_Process_AttributeAdd extends TestCase
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
        $filter = new \SimpleSAML\Module\core\Auth\Process\AttributeAdd($config, null);
        $filter->process($request);
        return $request;
    }

    /**
     * Test the most basic functionality.
     */
    public function testBasic()
    {
        $config = [
            'test' => ['value1', 'value2'],
        ];
        $request = [
            'Attributes' => [],
        ];
        $result = self::processFilter($config, $request);
        $attributes = $result['Attributes'];
        $this->assertArrayHasKey('test', $attributes);
        $this->assertEquals($attributes['test'], ['value1', 'value2']);
    }

    /**
     * Test that existing attributes are left unmodified.
     */
    public function testExistingNotModified()
    {
        $config = [
            'test' => ['value1', 'value2'],
        ];
        $request = [
            'Attributes' => [
                'original1' => ['original_value1'],
                'original2' => ['original_value2'],
            ],
        ];
        $result = self::processFilter($config, $request);
        $attributes = $result['Attributes'];
        $this->assertArrayHasKey('test', $attributes);
        $this->assertEquals($attributes['test'], ['value1', 'value2']);
        $this->assertArrayHasKey('original1', $attributes);
        $this->assertEquals($attributes['original1'], ['original_value1']);
        $this->assertArrayHasKey('original2', $attributes);
        $this->assertEquals($attributes['original2'], ['original_value2']);
    }

    /**
     * Test single string as attribute value.
     */
    public function testStringValue()
    {
        $config = [
            'test' => 'value',
        ];
        $request = [
            'Attributes' => [],
        ];
        $result = self::processFilter($config, $request);
        $attributes = $result['Attributes'];
        $this->assertArrayHasKey('test', $attributes);
        $this->assertEquals($attributes['test'], ['value']);
    }

    /**
     * Test adding multiple attributes in one config.
     */
    public function testAddMultiple()
    {
        $config = [
            'test1' => ['value1'],
            'test2' => ['value2'],
        ];
        $request = [
            'Attributes' => [],
        ];
        $result = self::processFilter($config, $request);
        $attributes = $result['Attributes'];
        $this->assertArrayHasKey('test1', $attributes);
        $this->assertEquals($attributes['test1'], ['value1']);
        $this->assertArrayHasKey('test2', $attributes);
        $this->assertEquals($attributes['test2'], ['value2']);
    }

    /**
     * Test behavior when appending attribute values.
     */
    public function testAppend()
    {
        $config = [
            'test' => ['value2'],
        ];
        $request = [
            'Attributes' => [
                'test' => ['value1'],
            ],
        ];
        $result = self::processFilter($config, $request);
        $attributes = $result['Attributes'];
        $this->assertEquals($attributes['test'], ['value1', 'value2']);
    }

    /**
     * Test replacing attribute values.
     */
    public function testReplace()
    {
        $config = [
            '%replace',
            'test' => ['value2'],
        ];
        $request = [
            'Attributes' => [
                'test' => ['value1'],
            ],
        ];
        $result = self::processFilter($config, $request);
        $attributes = $result['Attributes'];
        $this->assertEquals($attributes['test'], ['value2']);
    }

    /**
     * Test wrong usage generates exceptions
     *
     * @expectedException Exception
     */
    public function testWrongFlag()
    {
        $config = [
            '%nonsense',
            'test' => ['value2'],
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
            '%replace',
            'test' => [true],
        ];
        $request = [
            'Attributes' => [
                'test' => ['value1'],
            ],
        ];
        self::processFilter($config, $request);
    }
}
