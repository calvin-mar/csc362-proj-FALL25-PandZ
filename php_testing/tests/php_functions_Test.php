<?php
use PHPUnit\Framework\TestCase;

include 'php_testing/src/php_functions.php'; // adjust path as needed

class FakeMysqliResult
{
    public $num_rows;
    public $field_count;
    private $data;
    private $fields;

    public function __construct($data, $fields)
    {
        $this->data = $data;
        $this->fields = $fields;
        $this->num_rows = count($data);
        $this->field_count = count($fields);
    }

    public function fetch_all()
    {
        return $this->data;
    }

    public function fetch_fields()
    {
        return $this->fields;
    }
}

class php_functions_Test extends TestCase
{
    public function testResultToHtmlTableOutputsValidTable()
    {
        $data = [
            [1, 'Alice'],
            [2, 'Bob']
        ];
        $fields = [
            (object)['name' => 'id'],
            (object)['name' => 'name']
        ];

        $fakeResult = new FakeMysqliResult($data, $fields);

        // Capture the HTML output
        ob_start();
        result_to_html_table($fakeResult);
        $html = ob_get_clean();

        // Assertions
        $this->assertStringContainsString('<table>', $html);
        $this->assertStringContainsString('<thead>', $html);
        $this->assertStringContainsString('<tbody>', $html);
        $this->assertStringContainsString('<b>id</b>', $html);
        $this->assertStringContainsString('<b>name</b>', $html);
        $this->assertStringContainsString('<td>1</td>', $html);
        $this->assertStringContainsString('<td>Alice</td>', $html);
        $this->assertStringContainsString('<td>2</td>', $html);
        $this->assertStringContainsString('<td>Bob</td>', $html);
    }
}

