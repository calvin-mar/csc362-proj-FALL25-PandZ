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

class phpTest extends TestCase
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

/**
 * @covers zoning_functions.php
 */
require_once 'php_testing/src/zoning_functions.php';

/**
 * Mock mysqli and stmt to simulate DB calls
 */
class FakeStmt {
    public $error = '';
    public $executed = false;
    public $bound = false;

    public function bind_param() {
        $this->bound = true;
        return true;
    }

    public function execute() {
        $this->executed = true;
        return true;
    }

    public function close() {}
}

class FakeMysqli {
    public $error = '';
    public $prepareCalled = false;
    public $shouldFailPrepare = false;

    public function prepare($sql) {
        $this->prepareCalled = true;
        if ($this->shouldFailPrepare) {
            $this->error = 'prepare error';
            return false;
        }
        return new FakeStmt();
    }
}

/**
 * Test suite for zoning_functions.php
 */
class ZoningFunctionsTest extends TestCase
{
    public function testGetPostValueReturnsValueOrNull()
    {
        $post = ['x' => 'hello', 'empty' => ''];
        $this->assertEquals('hello', getPostValue($post, 'x'));
        $this->assertNull(getPostValue($post, 'empty'));
        $this->assertNull(getPostValue($post, 'missing'));
    }

    public function testGetPostArrayAsJsonReturnsJsonOrNull()
    {
        $post = ['arr' => ['a', 'b']];
        $this->assertEquals(json_encode(['a', 'b']), getPostArrayAsJson($post, 'arr'));
        $this->assertNull(getPostArrayAsJson([], 'arr'));
        $this->assertNull(getPostArrayAsJson(['arr' => 'not array'], 'arr'));
    }

    public function testExtractAdditionalOfficers()
    {
        $post = [
            'additional_applicant_officers_1' => ['a', 'b'],
            'additional_applicant_officers_2' => ['c'],
            'irrelevant' => ['x']
        ];
        $json = extractAdditionalOfficers($post);
        $decoded = json_decode($json, true);
        $this->assertCount(2, $decoded);
        $this->assertEquals(['a', 'b'], $decoded['1']);
    }

    public function testExtractFileDataReturnsContents()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'phpunit');
        file_put_contents($tmpFile, 'filedata');
        $files = ['f' => ['tmp_name' => $tmpFile, 'error' => UPLOAD_ERR_OK]];
        $this->assertEquals('filedata', extractFileData($files, 'f'));
    }

    public function testExtractFileDataReturnsNullIfError()
    {
        $files = ['f' => ['tmp_name' => 'none', 'error' => UPLOAD_ERR_NO_FILE]];
        $this->assertNull(extractFileData($files, 'f'));
    }

    public function testValidateZoningFormDataSuccess()
    {
        $form = [
            'applicant_name' => 'Alice',
            'p_zoning_map_amendment_request' => 'Change zoning',
            'applicant_email' => 'test@example.com'
        ];
        $result = validateZoningFormData($form);
        $this->assertTrue($result['valid']);
    }

    public function testValidateZoningFormDataFailure()
    {
        $form = [
            'applicant_email' => 'invalidemail',
            'owner_email' => 'ownerbad',
            'attorney_email' => 'attorneybad'
        ];
        $result = validateZoningFormData($form);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Invalid', $result['message']);
    }

    public function testInsertZoningMapAmendmentSuccess()
    {
        $conn = new FakeMysqli();
        $form = [
            'p_form_datetime_resolved' => null,
            'p_form_paid_bool' => 1,
            'p_correction_form_id' => 2,
            'p_zoning_map_amendment_request' => 'Request text'
        ];
        $result = insertZoningMapAmendment($form, $conn);
        $this->assertTrue($result['success']);
        $this->assertTrue($conn->prepareCalled);
    }

    public function testInsertZoningMapAmendmentPrepareFails()
    {
        $conn = new FakeMysqli();
        $conn->shouldFailPrepare = true;
        $form = [
            'p_form_datetime_resolved' => null,
            'p_form_paid_bool' => 1,
            'p_correction_form_id' => 2,
            'p_zoning_map_amendment_request' => 'Request text'
        ];
        $result = insertZoningMapAmendment($form, $conn);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('prepare failed', $result['message']);
    }

    public function testProcessZoningMapAmendmentValidFlow()
    {
        $conn = new FakeMysqli();
        $post = [
            'applicant_name' => 'Alice',
            'p_zoning_map_amendment_request' => 'Rezone area',
            'applicant_email' => 'alice@example.com'
        ];
        $files = [];
        $result = processZoningMapAmendment($post, $files, $conn);
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('successfully', $result['message']);
    }

    public function testProcessZoningMapAmendmentValidationFails()
    {
        $conn = new FakeMysqli();
        $post = [
            'p_zoning_map_amendment_request' => '',
            'applicant_name' => ''
        ];
        $files = [];
        $result = processZoningMapAmendment($post, $files, $conn);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('required', $result['message']);
    }

    public function testExtractZoningFormDataBuildsExpectedKeys()
    {
        $post = [
            'p_docket_number' => 'D001',
            'applicant_name' => 'Alice',
            'officers_names' => ['Bob'],
            'checklist_application' => 'on'
        ];
        $files = [];
        $data = extractZoningFormData($post, $files);
        $this->assertArrayHasKey('applicant_name', $data);
        $this->assertEquals(json_encode(['Bob']), $data['officers_names']);
        $this->assertEquals(1, $data['checklist_application']);
    }

    public function testExtractZoningFormDataHandlesMissingKeysGracefully()
    {
        $data = extractZoningFormData([], []);
        $this->assertArrayHasKey('applicant_name', $data);
        $this->assertNull($data['applicant_name']);
    }
}
