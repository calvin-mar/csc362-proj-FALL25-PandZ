<?php
use PHPUnit\Framework\TestCase;

include 'php_testing/src/php_functions.php'; // adjust path as needed
require_once __DIR__ . '/../../html/zoning_form_functions.php'; // adjust path to your functions file

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

class FakeMysqliConnection
{
    public $error = '';
    private $prepareResult;
    
    public function __construct($prepareResult = null, $error = '')
    {
        $this->prepareResult = $prepareResult;
        $this->error = $error;
    }
    
    public function prepare($sql)
    {
        return $this->prepareResult;
    }
    
    public function query($sql)
    {
        return false;
    }
}

class php_functions_Test extends TestCase
{
    // ==================== Original Test ====================
    
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
    
    // ==================== extractZoningFormData Tests ====================
    
    public function testExtractZoningFormDataWithFullData()
    {
        $post = [
            'p_form_datetime_resolved' => '2024-01-15 10:30:00',
            'p_correction_form_id' => '123',
            'p_zva_letter_content' => 'Letter content here',
            'p_zva_zoning_letter_street' => '123 Main St',
            'p_zva_zoning_letter_city' => 'Springfield',
            'p_zva_state_code' => 'IL',
            'p_zva_zoning_letter_zip' => '62701',
            'p_zva_property_street' => '456 Oak Ave',
            'p_property_city' => 'Springfield',
            'p_zva_property_state_code' => 'IL',
            'p_zva_property_zip' => '62702',
            'p_zva_applicant_first_name' => 'John',
            'p_zva_applicant_last_name' => 'Doe',
            'p_zva_applicant_street' => '789 Elm St',
            'p_zva_applicant_city' => 'Springfield',
            'p_zva_applicant_state_code' => 'IL',
            'p_zva_applicant_zip_code' => '62703',
            'p_zva_applicant_phone_number' => '555-1234',
            'p_zva_applicant_fax_number' => '555-5678',
            'p_zva_owner_first_name' => 'Jane',
            'p_zva_owner_last_name' => 'Smith',
            'p_zva_owner_street' => '321 Pine St',
            'p_zva_owner_city' => 'Springfield',
            'p_zva_owner_state_code' => 'IL',
            'p_zva_owner_zip_code' => '62704'
        ];
        
        $result = extractZoningFormData($post);
        
        $this->assertEquals('2024-01-15 10:30:00', $result['p_form_datetime_resolved']);
        $this->assertEquals(0, $result['p_form_paid_bool']);
        $this->assertEquals('123', $result['p_correction_form_id']);
        $this->assertEquals('Letter content here', $result['p_zva_letter_content']);
        $this->assertEquals('John', $result['p_zva_applicant_first_name']);
        $this->assertEquals('Jane', $result['p_zva_owner_first_name']);
    }
    
    public function testExtractZoningFormDataWithEmptyStrings()
    {
        $post = [
            'p_form_datetime_resolved' => '',
            'p_correction_form_id' => '',
            'p_zva_letter_content' => '',
        ];
        
        $result = extractZoningFormData($post);
        
        $this->assertNull($result['p_form_datetime_resolved']);
        $this->assertNull($result['p_correction_form_id']);
        $this->assertNull($result['p_zva_letter_content']);
    }
    
    public function testExtractZoningFormDataWithMissingFields()
    {
        $post = [
            'p_zva_applicant_first_name' => 'John'
        ];
        
        $result = extractZoningFormData($post);
        
        $this->assertEquals('John', $result['p_zva_applicant_first_name']);
        $this->assertNull($result['p_zva_applicant_last_name']);
        $this->assertNull($result['p_correction_form_id']);
    }
    
    public function testExtractZoningFormDataAlwaysSetsFormPaidBoolToZero()
    {
        $post = [];
        $result = extractZoningFormData($post);
        
        $this->assertEquals(0, $result['p_form_paid_bool']);
    }
    
    // ==================== validateZoningFormData Tests ====================
    
    public function testValidateZoningFormDataWithValidData()
    {
        $formData = [
            'p_zva_property_street' => '123 Main St',
            'p_property_city' => 'Springfield',
            'p_zva_property_state_code' => 'IL',
            'p_zva_property_zip' => '62701',
        ];
        
        $errors = validateZoningFormData($formData);
        
        $this->assertEmpty($errors);
    }
    
    public function testValidateZoningFormDataWithMissingRequiredFields()
    {
        $formData = [
            'p_zva_property_street' => '',
            'p_property_city' => null,
        ];
        
        $errors = validateZoningFormData($formData);
        
        $this->assertNotEmpty($errors);
        $this->assertContains('Property street address is required', $errors);
        $this->assertContains('Property city is required', $errors);
        $this->assertContains('Property state is required', $errors);
        $this->assertContains('Property ZIP code is required', $errors);
    }
    
    public function testValidateZoningFormDataWithInvalidZipCode()
    {
        $formData = [
            'p_zva_property_street' => '123 Main St',
            'p_property_city' => 'Springfield',
            'p_zva_property_state_code' => 'IL',
            'p_zva_property_zip' => 'ABCDE',
        ];
        
        $errors = validateZoningFormData($formData);
        
        $this->assertContains('Property ZIP code must be in format 12345 or 12345-6789', $errors);
    }
    
    public function testValidateZoningFormDataWithValidZipCodeFormats()
    {
        $formData1 = [
            'p_zva_property_street' => '123 Main St',
            'p_property_city' => 'Springfield',
            'p_zva_property_state_code' => 'IL',
            'p_zva_property_zip' => '62701',
        ];
        
        $errors1 = validateZoningFormData($formData1);
        $this->assertEmpty($errors1);
        
        $formData2 = [
            'p_zva_property_street' => '123 Main St',
            'p_property_city' => 'Springfield',
            'p_zva_property_state_code' => 'IL',
            'p_zva_property_zip' => '62701-1234',
        ];
        
        $errors2 = validateZoningFormData($formData2);
        $this->assertEmpty($errors2);
    }
    
    public function testValidateZoningFormDataWithInvalidPhoneNumber()
    {
        $formData = [
            'p_zva_property_street' => '123 Main St',
            'p_property_city' => 'Springfield',
            'p_zva_property_state_code' => 'IL',
            'p_zva_property_zip' => '62701',
            'p_zva_applicant_phone_number' => 'not-a-phone',
        ];
        
        $errors = validateZoningFormData($formData);
        
        $this->assertContains('Applicant phone number format is invalid', $errors);
    }
    
    public function testValidateZoningFormDataWithValidPhoneNumbers()
    {
        $formData = [
            'p_zva_property_street' => '123 Main St',
            'p_property_city' => 'Springfield',
            'p_zva_property_state_code' => 'IL',
            'p_zva_property_zip' => '62701',
            'p_zva_applicant_phone_number' => '(555) 123-4567',
        ];
        
        $errors = validateZoningFormData($formData);
        $this->assertEmpty($errors);
        
        $formData['p_zva_applicant_phone_number'] = '+1-555-123-4567';
        $errors = validateZoningFormData($formData);
        $this->assertEmpty($errors);
    }
    
    // ==================== formatAddress Tests ====================
    
    public function testFormatAddressWithAllComponents()
    {
        $result = formatAddress('123 Main St', 'Springfield', 'IL', '62701');
        
        $this->assertEquals('123 Main St, Springfield, IL, 62701', $result);
    }
    
    public function testFormatAddressWithMissingComponents()
    {
        $result = formatAddress('123 Main St', null, 'IL', '62701');
        
        $this->assertEquals('123 Main St, IL, 62701', $result);
    }
    
    public function testFormatAddressWithAllNullComponents()
    {
        $result = formatAddress(null, null, null, null);
        
        $this->assertEquals('', $result);
    }
    
    public function testFormatAddressWithEmptyStrings()
    {
        $result = formatAddress('', '', 'IL', '');
        
        $this->assertEquals('IL', $result);
    }
    
    // ==================== sanitizePhoneNumber Tests ====================
    
    public function testSanitizePhoneNumberWithDashesAndParentheses()
    {
        $result = sanitizePhoneNumber('(555) 123-4567');
        
        $this->assertEquals('5551234567', $result);
    }
    
    public function testSanitizePhoneNumberWithInternationalFormat()
    {
        $result = sanitizePhoneNumber('+1-555-123-4567');
        
        $this->assertEquals('+15551234567', $result);
    }
    
    public function testSanitizePhoneNumberWithSpaces()
    {
        $result = sanitizePhoneNumber('555 123 4567');
        
        $this->assertEquals('5551234567', $result);
    }
    
    public function testSanitizePhoneNumberWithNull()
    {
        $result = sanitizePhoneNumber(null);
        
        $this->assertNull($result);
    }
    
    public function testSanitizePhoneNumberWithEmptyString()
    {
        $result = sanitizePhoneNumber('');
        
        $this->assertNull($result);
    }
    
    public function testSanitizePhoneNumberWithOnlySpecialCharacters()
    {
        $result = sanitizePhoneNumber('()- ');
        
        $this->assertNull($result);
    }
    
    // ==================== fetchStateCodes Tests (requires mock) ====================
    
    public function testFetchStateCodesReturnsArray()
    {
        // Create a mock mysqli connection
        $mockConn = $this->createMock(mysqli::class);
        
        // Create mock result
        $mockResult = $this->createMock(mysqli_result::class);
        $mockResult->method('fetch_assoc')
            ->willReturnOnConsecutiveCalls(
                ['state_code' => 'AL'],
                ['state_code' => 'CA'],
                ['state_code' => 'TX'],
                null
            );
        
        $mockConn->method('query')
            ->willReturn($mockResult);
        
        $result = fetchStateCodes($mockConn);
        
        $this->assertIsArray($result);
        $this->assertEquals(['AL', 'CA', 'TX'], $result);
    }
    
    public function testFetchStateCodesWithNoResults()
    {
        $mockConn = $this->createMock(mysqli::class);
        $mockConn->method('query')
            ->willReturn(false);
        
        $result = fetchStateCodes($mockConn);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
    
    // ==================== insertZoningVerificationApplication Tests ====================
    
    public function testInsertZoningVerificationApplicationWithPrepareFailure()
    {
        $fakeConn = new FakeMysqliConnection(false, 'Prepare error message');
        
        $formData = extractZoningFormData([]);
        $result = insertZoningVerificationApplication($fakeConn, $formData);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Prepare failed', $result['message']);
        $this->assertStringContainsString('Prepare error message', $result['message']);
    }
    
    // ==================== extractZoningMapAmendmentFormData Tests ====================
    
    public function testExtractZoningMapAmendmentFormDataWithFullData()
    {
        $post = [
            'p_form_datetime_resolved' => '2024-01-15 10:30:00',
            'p_form_paid_bool' => '1',
            'p_correction_form_id' => '123',
            'applicant_name' => 'Test Company LLC',
            'officers_names' => ['John Doe', 'Jane Smith'],
            'applicant_street' => '123 Main St',
            'property_street' => '456 Oak Ave',
            'property_city' => 'Springfield',
            'property_state' => 'IL',
            'checklist_application' => '1',
            'checklist_fees' => '1',
        ];
        
        $files = [
            'file_exhibit' => ['name' => 'exhibit.pdf', 'error' => UPLOAD_ERR_OK],
            'file_adjacent' => ['name' => 'adjacent.pdf', 'error' => UPLOAD_ERR_OK],
        ];
        
        $result = extractZoningMapAmendmentFormData($post, $files);
        
        $this->assertEquals('2024-01-15 10:30:00', $result['p_form_datetime_resolved']);
        $this->assertEquals(1, $result['p_form_paid_bool']);
        $this->assertEquals(123, $result['p_correction_form_id']);
        $this->assertEquals('Test Company LLC', $result['p_applicant_name']);
        $this->assertEquals('exhibit.pdf', $result['p_file_exhibit']);
        $this->assertEquals('adjacent.pdf', $result['p_file_adjacent']);
        $this->assertEquals(1, $result['p_checklist_application']);
        $this->assertEquals(1, $result['p_checklist_fees']);
        $this->assertEquals(0, $result['p_checklist_exhibit']);
    }
    
    public function testExtractZoningMapAmendmentFormDataWithAdditionalApplicantOfficers()
    {
        $post = [
            'additional_applicant_officers_1' => ['Officer A', 'Officer B'],
            'additional_applicant_officers_2' => ['Officer C'],
        ];
        
        $result = extractZoningMapAmendmentFormData($post);
        
        $this->assertNotNull($result['p_additional_applicant_officers']);
        $decoded = json_decode($result['p_additional_applicant_officers'], true);
        $this->assertArrayHasKey('1', $decoded);
        $this->assertArrayHasKey('2', $decoded);
        $this->assertContains('Officer A', $decoded['1']);
        $this->assertContains('Officer C', $decoded['2']);
    }
    
    public function testExtractZoningMapAmendmentFormDataWithEmptyFiles()
    {
        $post = [];
        $files = [
            'file_exhibit' => ['name' => '', 'error' => UPLOAD_ERR_NO_FILE],
        ];
        
        $result = extractZoningMapAmendmentFormData($post, $files);
        
        $this->assertNull($result['p_file_exhibit']);
    }
    
    // ==================== convertArrayToJson Tests ====================
    
    public function testConvertArrayToJsonWithValidArray()
    {
        $array = ['value1', 'value2', 'value3'];
        $result = convertArrayToJson($array);
        
        $this->assertEquals('["value1","value2","value3"]', $result);
    }
    
    public function testConvertArrayToJsonWithEmptyValues()
    {
        $array = ['value1', '', null, 'value2'];
        $result = convertArrayToJson($array, true);
        
        $decoded = json_decode($result, true);
        $this->assertContains('value1', $decoded);
        $this->assertContains('value2', $decoded);
        $this->assertNotContains('', $decoded);
    }
    
    public function testConvertArrayToJsonWithoutFiltering()
    {
        $array = ['value1', '', 'value2'];
        $result = convertArrayToJson($array, false);
        
        $decoded = json_decode($result, true);
        $this->assertCount(3, $decoded);
        $this->assertEquals('', $decoded[1]);
    }
    
    public function testConvertArrayToJsonWithEmptyArray()
    {
        $result = convertArrayToJson([]);
        $this->assertNull($result);
    }
    
    // ==================== extractUploadedFileName Tests ====================
    
    public function testExtractUploadedFileNameWithValidUpload()
    {
        $files = [
            'file_exhibit' => [
                'name' => 'document.pdf',
                'error' => UPLOAD_ERR_OK
            ]
        ];
        
        $result = extractUploadedFileName($files, 'file_exhibit');
        
        $this->assertEquals('document.pdf', $result);
    }
    
    public function testExtractUploadedFileNameWithUploadError()
    {
        $files = [
            'file_exhibit' => [
                'name' => 'document.pdf',
                'error' => UPLOAD_ERR_NO_FILE
            ]
        ];
        
        $result = extractUploadedFileName($files, 'file_exhibit');
        
        $this->assertNull($result);
    }
    
    public function testExtractUploadedFileNameWithMissingField()
    {
        $files = [];
        
        $result = extractUploadedFileName($files, 'file_exhibit');
        
        $this->assertNull($result);
    }
    
    // ==================== validateZoningMapAmendmentData Tests ====================
    
    public function testValidateZoningMapAmendmentDataWithValidData()
    {
        $formData = [
            'p_property_street' => '123 Main St',
            'p_property_city' => 'Springfield',
            'p_property_state' => 'IL',
            'p_property_zip_code' => '62701',
            'p_applicant_email' => 'test@example.com',
        ];
        
        $errors = validateZoningMapAmendmentData($formData);
        
        $this->assertEmpty($errors);
    }
    
    public function testValidateZoningMapAmendmentDataWithMissingRequired()
    {
        $formData = [
            'p_property_street' => '',
        ];
        
        $errors = validateZoningMapAmendmentData($formData);
        
        $this->assertNotEmpty($errors);
        $this->assertContains('Property street address is required', $errors);
        $this->assertContains('Property city is required', $errors);
    }
    
    public function testValidateZoningMapAmendmentDataWithInvalidEmail()
    {
        $formData = [
            'p_property_street' => '123 Main St',
            'p_property_city' => 'Springfield',
            'p_property_state' => 'IL',
            'p_applicant_email' => 'not-an-email',
        ];
        
        $errors = validateZoningMapAmendmentData($formData);
        
        $this->assertContains('Applicant email format is invalid', $errors);
    }
    
    public function testValidateZoningMapAmendmentDataWithInvalidPhone()
    {
        $formData = [
            'p_property_street' => '123 Main St',
            'p_property_city' => 'Springfield',
            'p_property_state' => 'IL',
            'p_applicant_phone' => 'not-a-phone',
        ];
        
        $errors = validateZoningMapAmendmentData($formData);
        
        $this->assertContains('Applicant phone format is invalid', $errors);
    }
    
    // ==================== linkFormToClient Tests ====================
    
    public function testLinkFormToClientWithPrepareFailure()
    {
        $fakeConn = new FakeMysqliConnection(false, 'Prepare error');
        
        $result = linkFormToClient($fakeConn, 1, 1);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Prepare failed', $result['message']);
    }
    
    // ==================== extractVarianceApplicationFormData Tests ====================
    
    public function testExtractVarianceApplicationFormDataWithFullData()
    {
        $post = [
            'p_form_datetime_resolved' => '2024-01-15 10:30:00',
            'p_correction_form_id' => '123',
            'p_docket_number' => 'VAR-2024-001',
            'p_applicant_name' => 'John Doe',
            'p_officers_names' => ['Officer 1', 'Officer 2'],
            'p_variance_request' => 'Request for setback variance',
            'p_checklist_application' => '1',
            'p_checklist_fees' => '1',
            'p_signature_name_1' => 'John Doe',
            'p_signature_date_1' => '2024-01-15',
        ];
        
        $files = [
            'p_file_exhibit' => ['name' => 'site_plan.pdf', 'error' => UPLOAD_ERR_OK],
        ];
        
        $result = extractVarianceApplicationFormData($post, $files);
        
        $this->assertEquals('2024-01-15 10:30:00', $result['p_form_datetime_resolved']);
        $this->assertEquals(0, $result['p_form_paid_bool']);
        $this->assertEquals(123, $result['p_correction_form_id']);
        $this->assertEquals('VAR-2024-001', $result['p_docket_number']);
        $this->assertEquals('John Doe', $result['p_applicant_name']);
        $this->assertEquals('Request for setback variance', $result['p_variance_request']);
        $this->assertEquals('site_plan.pdf', $result['p_file_exhibit']);
        $this->assertEquals(1, $result['p_checklist_application']);
        $this->assertEquals(1, $result['p_checklist_fees']);
        $this->assertEquals(0, $result['p_checklist_exhibit']);
    }
    
    public function testExtractVarianceApplicationFormDataAlwaysSetsFormPaidBoolToZero()
    {
        $post = [];
        $result = extractVarianceApplicationFormData($post);
        
        $this->assertEquals(0, $result['p_form_paid_bool']);
    }
    
    public function testExtractVarianceApplicationFormDataWithOfficersArray()
    {
        $post = [
            'p_officers_names' => ['John Smith', 'Jane Doe', ''],
        ];
        
        $result = extractVarianceApplicationFormData($post);
        
        $this->assertNotNull($result['p_officers_names']);
        $decoded = json_decode($result['p_officers_names'], true);
        $this->assertContains('John Smith', $decoded);
        $this->assertContains('Jane Doe', $decoded);
        $this->assertNotContains('', $decoded);
    }
    
    // ==================== convertNestedArrayToJson Tests ====================
    
    public function testConvertNestedArrayToJsonWithValidData()
    {
        $array = [
            0 => ['Officer A', 'Officer B'],
            1 => ['Officer C'],
        ];
        
        $result = convertNestedArrayToJson($array);
        
        $this->assertNotNull($result);
        $decoded = json_decode($result, true);
        $this->assertArrayHasKey(0, $decoded);
        $this->assertArrayHasKey(1, $decoded);
        $this->assertContains('Officer A', $decoded[0]);
    }
    
    public function testConvertNestedArrayToJsonFiltersEmptyArrays()
    {
        $array = [
            0 => ['Officer A'],
            1 => [],
            2 => [''],
        ];
        
        $result = convertNestedArrayToJson($array);
        
        $decoded = json_decode($result, true);
        $this->assertArrayHasKey(0, $decoded);
        $this->assertArrayNotHasKey(1, $decoded);
        $this->assertArrayNotHasKey(2, $decoded);
    }
    
    public function testConvertNestedArrayToJsonWithEmptyInput()
    {
        $result = convertNestedArrayToJson([]);
        $this->assertNull($result);
    }
    
    // ==================== validateVarianceApplicationData Tests ====================
    
    public function testValidateVarianceApplicationDataWithValidData()
    {
        $formData = [
            'p_applicant_name' => 'John Doe',
            'p_variance_request' => 'Request for setback variance',
            'p_signature_name_1' => 'John Doe',
            'p_signature_date_1' => '2024-01-15',
            'p_applicant_email' => 'john@example.com',
            'p_property_zip_code' => '42001',
        ];
        
        $errors = validateVarianceApplicationData($formData);
        
        $this->assertEmpty($errors);
    }
    
    public function testValidateVarianceApplicationDataWithMissingRequired()
    {
        $formData = [];
        
        $errors = validateVarianceApplicationData($formData);
        
        $this->assertNotEmpty($errors);
        $this->assertContains('Applicant name is required', $errors);
        $this->assertContains('Variance request description is required', $errors);
        $this->assertContains('At least one signature is required', $errors);
        $this->assertContains('At least one signature date is required', $errors);
    }
    
    public function testValidateVarianceApplicationDataWithInvalidEmail()
    {
        $formData = [
            'p_applicant_name' => 'John Doe',
            'p_variance_request' => 'Setback variance',
            'p_signature_name_1' => 'John Doe',
            'p_signature_date_1' => '2024-01-15',
            'p_applicant_email' => 'invalid-email',
        ];
        
        $errors = validateVarianceApplicationData($formData);
        
        $this->assertContains('Applicant email format is invalid', $errors);
    }
    
    public function testValidateVarianceApplicationDataWithInvalidZipCode()
    {
        $formData = [
            'p_applicant_name' => 'John Doe',
            'p_variance_request' => 'Setback variance',
            'p_signature_name_1' => 'John Doe',
            'p_signature_date_1' => '2024-01-15',
            'p_property_zip_code' => 'ABCDE',
        ];
        
        $errors = validateVarianceApplicationData($formData);
        
        $this->assertContains('Property ZIP code must be in format 12345 or 12345-6789', $errors);
    }
    
    public function testValidateVarianceApplicationDataWithInvalidPhone()
    {
        $formData = [
            'p_applicant_name' => 'John Doe',
            'p_variance_request' => 'Setback variance',
            'p_signature_name_1' => 'John Doe',
            'p_signature_date_1' => '2024-01-15',
            'p_applicant_phone' => 'not-a-phone',
        ];
        
        $errors = validateVarianceApplicationData($formData);
        
        $this->assertContains('Applicant phone format is invalid', $errors);
    }
    
    // ==================== extractSiteDevelopmentPlanFormData Tests ====================
    
    public function testExtractSiteDevelopmentPlanFormDataWithFullData()
    {
        $post = [
            'applicant_name' => 'ABC Development LLC',
            'officers_names' => ['John Doe', 'Jane Smith'],
            'surveyor_name' => 'Robert Johnson',
            'engineer_name' => 'Mary Williams',
            'application_type' => 'new',
            'site_plan_request' => 'Residential development',
            'checklist_application' => '1',
            'checklist_fees' => '1',
            'signature_name_1' => 'John Doe',
            'signature_date_1' => '2024-01-15',
        ];
        
        $files = [
            'file_verification' => ['name' => 'verify.pdf', 'error' => UPLOAD_ERR_OK, 'tmp_name' => '/tmp/test'],
        ];
        
        $result = extractSiteDevelopmentPlanFormData($post, $files);
        
        $this->assertEquals('ABC Development LLC', $result['applicant_name']);
        $this->assertEquals('new', $result['application_type']);
        $this->assertEquals('Residential development', $result['site_plan_request']);
        $this->assertEquals('Robert', $result['surveyor_first_name']);
        $this->assertEquals('Johnson', $result['surveyor_last_name']);
        $this->assertEquals('Mary', $result['engineer_first_name']);
        $this->assertEquals('Williams', $result['engineer_last_name']);
        $this->assertEquals(1, $result['checklist_application']);
        $this->assertEquals(1, $result['checklist_fees']);
        $this->assertEquals(0, $result['checklist_verification']);
    }
    
    public function testExtractSiteDevelopmentPlanFormDataWithOfficers()
    {
        $post = [
            'officers_names' => ['Alice Anderson', '', 'Bob Brown'],
        ];
        
        $result = extractSiteDevelopmentPlanFormData($post);
        
        $this->assertNotNull($result['officers_names']);
        $decoded = json_decode($result['officers_names'], true);
        $this->assertContains('Alice Anderson', $decoded);
        $this->assertContains('Bob Brown', $decoded);
        $this->assertNotContains('', $decoded);
    }
    
    // ==================== splitFirstName Tests ====================
    
    public function testSplitFirstNameWithFullName()
    {
        $result = splitFirstName('John Doe');
        $this->assertEquals('John', $result);
    }
    
    public function testSplitFirstNameWithMultipleNames()
    {
        $result = splitFirstName('Mary Jane Smith');
        $this->assertEquals('Mary', $result);
    }
    
    public function testSplitFirstNameWithSingleName()
    {
        $result = splitFirstName('John');
        $this->assertEquals('John', $result);
    }
    
    public function testSplitFirstNameWithNull()
    {
        $result = splitFirstName(null);
        $this->assertNull($result);
    }
    
    public function testSplitFirstNameWithEmptyString()
    {
        $result = splitFirstName('');
        $this->assertNull($result);
    }
    
    // ==================== splitLastName Tests ====================
    
    public function testSplitLastNameWithFullName()
    {
        $result = splitLastName('John Doe');
        $this->assertEquals('Doe', $result);
    }
    
    public function testSplitLastNameWithMultipleNames()
    {
        $result = splitLastName('Mary Jane Smith');
        $this->assertEquals('Jane Smith', $result);
    }
    
    public function testSplitLastNameWithSingleName()
    {
        $result = splitLastName('John');
        $this->assertEquals('', $result);
    }
    
    public function testSplitLastNameWithNull()
    {
        $result = splitLastName(null);
        $this->assertNull($result);
    }
    
    // ==================== validateSiteDevelopmentPlanData Tests ====================
    
    public function testValidateSiteDevelopmentPlanDataWithValidData()
    {
        $formData = [
            'applicant_name' => 'ABC Development',
            'signature_name_1' => 'John Doe',
            'signature_date_1' => '2024-01-15',
            'applicant_email' => 'john@example.com',
            'applicant_zip_code' => '42001',
        ];
        
        $errors = validateSiteDevelopmentPlanData($formData);
        
        $this->assertEmpty($errors);
    }
    
    public function testValidateSiteDevelopmentPlanDataWithMissingRequired()
    {
        $formData = [];
        
        $errors = validateSiteDevelopmentPlanData($formData);
        
        $this->assertNotEmpty($errors);
        $this->assertContains('Applicant name is required', $errors);
        $this->assertContains('At least one signature is required', $errors);
        $this->assertContains('At least one signature date is required', $errors);
    }
    
    public function testValidateSiteDevelopmentPlanDataWithInvalidEmail()
    {
        $formData = [
            'applicant_name' => 'ABC Development',
            'signature_name_1' => 'John Doe',
            'signature_date_1' => '2024-01-15',
            'surveyor_email' => 'invalid-email',
        ];
        
        $errors = validateSiteDevelopmentPlanData($formData);
        
        $this->assertContains('Surveyor email format is invalid', $errors);
    }
    
    public function testValidateSiteDevelopmentPlanDataWithMultipleInvalidPhones()
    {
        $formData = [
            'applicant_name' => 'ABC Development',
            'signature_name_1' => 'John Doe',
            'signature_date_1' => '2024-01-15',
            'surveyor_phone' => 'not-valid',
            'engineer_cell' => 'also-invalid',
        ];
        
        $errors = validateSiteDevelopmentPlanData($formData);
        
        $this->assertContains('Surveyor phone format is invalid', $errors);
        $this->assertContains('Engineer cell format is invalid', $errors);
    }

  // APPEND THESE TESTS TO THE END OF php_functions_Test.php (before the closing brace)

    // ==================== Sign Permit Application Tests ====================
    
    // ==================== extractSignPermitFormData Tests ====================
    
    public function testExtractSignPermitFormDataWithFullData()
    {
        $post = [
            'p_form_datetime_resolved' => '2024-01-15 10:30:00',
            'p_form_paid_bool' => '1',
            'p_date' => '2024-01-15',
            'p_permit_number' => 'SP-2024-001',
            'building_coverage' => '25%',
            'total_permit_fee' => '$150.00',
            'property_owner' => 'John Doe',
            'property_owner_address' => '123 Main St',
            'property_owner_city' => 'Murray',
            'property_owner_state_code' => 'KY',
            'property_owner_zip_code' => '42071',
            'business_name' => 'Acme Corp',
            'business_address' => '456 Business Rd',
            'business_city' => 'Murray',
            'business_state_code' => 'KY',
            'business_zip_code' => '42071',
            'agent_applicant' => 'Jane Smith',
            'applicant_address' => '789 Agent Ave',
            'applicant_city' => 'Murray',
            'applicant_state_code' => 'KY',
            'applicant_zip_code' => '42071',
            'contractor' => 'Bob Builder',
            'contractor_phone' => '(555) 123-4567',
            'sign_type_freestanding' => '1',
            'sign_type_wall_mounted' => '1',
            'square_footage' => '25.5',
            'lettering_height' => '12 inches',
            'sign_number_freestanding' => '2',
            'sign_number_wall_mounted' => '1',
        ];
        
        $result = extractSignPermitFormData($post);
        
        $this->assertEquals('2024-01-15 10:30:00', $result['p_form_datetime_resolved']);
        $this->assertEquals(1, $result['p_form_paid_bool']);
        $this->assertEquals('2024-01-15', $result['p_sp_date']);
        $this->assertEquals('SP-2024-001', $result['p_sp_permit_number']);
        $this->assertEquals('25%', $result['p_sp_building_coverage_percent']);
        $this->assertEquals('150.00', $result['p_sp_permit_fee']);
        $this->assertEquals('John', $result['p_sp_owner_first_name']);
        $this->assertEquals('Doe', $result['p_sp_owner_last_name']);
        $this->assertEquals('Acme Corp', $result['p_sp_business_name']);
        $this->assertEquals('Bob', $result['p_sp_contractor_first_name']);
        $this->assertEquals('Builder', $result['p_sp_contractor_last_name']);
        $this->assertEquals('Free-Standing, Wall-Mounted', $result['p_sign_type']);
        $this->assertEquals(25.5, $result['p_sign_square_footage']);
        $this->assertEquals('12 inches', $result['p_lettering_height']);
        $this->assertEquals(2, $result['p_sign_number_freestanding']);
        $this->assertEquals(1, $result['p_sign_number_wall_mounted']);
    }
    
    public function testExtractSignPermitFormDataWithDefaultDate()
    {
        $post = [];
        
        $result = extractSignPermitFormData($post);
        
        $this->assertEquals(date('Y-m-d'), $result['p_sp_date']);
    }
    
    public function testExtractSignPermitFormDataWithAllSignTypes()
    {
        $post = [
            'sign_type_freestanding' => '1',
            'sign_type_wall_mounted' => '1',
            'sign_type_temporary' => '1',
            'sign_type_directional' => '1',
        ];
        
        $result = extractSignPermitFormData($post);
        
        $this->assertEquals('Free-Standing, Wall-Mounted, Temporary, Directional', $result['p_sign_type']);
    }
    
    public function testExtractSignPermitFormDataWithNoSignTypes()
    {
        $post = [];
        
        $result = extractSignPermitFormData($post);
        
        $this->assertNull($result['p_sign_type']);
    }
    
    public function testExtractSignPermitFormDataWithChecklistItems()
    {
        $post = [
            'checklist_sign_specs' => '1',
            'checklist_location_drawing' => '1',
        ];
        
        $result = extractSignPermitFormData($post);
        
        $this->assertEquals(1, $result['p_checklist_sign_specs']);
        $this->assertEquals(1, $result['p_checklist_location_drawing']);
    }
    
    public function testExtractSignPermitFormDataWithFileUploads()
    {
        $post = [];
        $files = [
            'file_sign_specs' => ['name' => 'specs.pdf', 'error' => UPLOAD_ERR_OK],
            'file_location_drawing' => ['name' => 'drawing.pdf', 'error' => UPLOAD_ERR_OK],
            'file_building_facia' => ['name' => 'facia.pdf', 'error' => UPLOAD_ERR_OK],
        ];
        
        $result = extractSignPermitFormData($post, $files);
        
        $this->assertEquals('specs.pdf', $result['p_file_sign_specs']);
        $this->assertEquals('drawing.pdf', $result['p_file_location_drawing']);
        $this->assertEquals('facia.pdf', $result['p_file_building_facia']);
    }
    
    public function testExtractSignPermitFormDataWithSquareFootageConversion()
    {
        $post = [
            'square_footage' => '42.75',
        ];
        
        $result = extractSignPermitFormData($post);
        
        $this->assertEquals(42.75, $result['p_sign_square_footage']);
        $this->assertIsFloat($result['p_sign_square_footage']);
    }
    
    // ==================== validateSignPermitData Tests ====================
    
    public function testValidateSignPermitDataWithValidData()
    {
        $formData = [
            'p_sp_business_name' => 'Acme Corp',
            'p_sp_owner_first_name' => 'John',
            'p_sp_owner_last_name' => 'Doe',
            'p_sp_business_street' => '456 Business Rd',
            'p_sp_business_city' => 'Murray',
            'p_sp_business_state_code' => 'KY',
            'p_sp_owner_street' => '123 Main St',
            'p_sp_owner_city' => 'Murray',
            'p_sp_owner_state_code' => 'KY',
            'p_sp_owner_zip_code' => '42071',
        ];
        
        $errors = validateSignPermitData($formData);
        
        $this->assertEmpty($errors);
    }
    
    public function testValidateSignPermitDataWithMissingRequiredFields()
    {
        $formData = [
            'p_sp_business_name' => '',
            'p_sp_owner_first_name' => null,
        ];
        
        $errors = validateSignPermitData($formData);
        
        $this->assertNotEmpty($errors);
        $this->assertContains('Business name is required', $errors);
        $this->assertContains('Property owner name is required', $errors);
    }
    
    public function testValidateSignPermitDataWithInvalidZipCode()
    {
        $formData = [
            'p_sp_business_name' => 'Acme Corp',
            'p_sp_owner_first_name' => 'John',
            'p_sp_business_street' => '456 Business Rd',
            'p_sp_business_city' => 'Murray',
            'p_sp_business_state_code' => 'KY',
            'p_sp_owner_street' => '123 Main St',
            'p_sp_owner_city' => 'Murray',
            'p_sp_owner_state_code' => 'KY',
            'p_sp_business_zip_code' => 'ABCDE',
        ];
        
        $errors = validateSignPermitData($formData);
        
        $this->assertContains('Business ZIP code must be in format 12345 or 12345-6789', $errors);
    }
    
    public function testValidateSignPermitDataWithValidZipCodeFormats()
    {
        $formData = [
            'p_sp_business_name' => 'Acme Corp',
            'p_sp_owner_first_name' => 'John',
            'p_sp_business_street' => '456 Business Rd',
            'p_sp_business_city' => 'Murray',
            'p_sp_business_state_code' => 'KY',
            'p_sp_owner_street' => '123 Main St',
            'p_sp_owner_city' => 'Murray',
            'p_sp_owner_state_code' => 'KY',
            'p_sp_owner_zip_code' => '42071',
            'p_sp_business_zip_code' => '42071-1234',
        ];
        
        $errors = validateSignPermitData($formData);
        
        $this->assertEmpty($errors);
    }
    
    public function testValidateSignPermitDataWithInvalidPhoneNumber()
    {
        $formData = [
            'p_sp_business_name' => 'Acme Corp',
            'p_sp_owner_first_name' => 'John',
            'p_sp_business_street' => '456 Business Rd',
            'p_sp_business_city' => 'Murray',
            'p_sp_business_state_code' => 'KY',
            'p_sp_owner_street' => '123 Main St',
            'p_sp_owner_city' => 'Murray',
            'p_sp_owner_state_code' => 'KY',
            'p_sp_contractor_phone_number' => 'not-a-phone',
        ];
        
        $errors = validateSignPermitData($formData);
        
        $this->assertContains('Contractor phone number format is invalid', $errors);
    }
    
    public function testValidateSignPermitDataWithValidPhoneNumber()
    {
        $formData = [
            'p_sp_business_name' => 'Acme Corp',
            'p_sp_owner_first_name' => 'John',
            'p_sp_business_street' => '456 Business Rd',
            'p_sp_business_city' => 'Murray',
            'p_sp_business_state_code' => 'KY',
            'p_sp_owner_street' => '123 Main St',
            'p_sp_owner_city' => 'Murray',
            'p_sp_owner_state_code' => 'KY',
            'p_sp_contractor_phone_number' => '(555) 123-4567',
        ];
        
        $errors = validateSignPermitData($formData);
        
        $this->assertEmpty($errors);
    }
    
    // ==================== parseFullName Tests ====================
    
    public function testParseFullNameWithFullName()
    {
        $result = parseFullName('John Doe');
        
        $this->assertEquals('John', $result['first_name']);
        $this->assertEquals('Doe', $result['last_name']);
    }
    
    public function testParseFullNameWithMultipleNames()
    {
        $result = parseFullName('Mary Jane Smith');
        
        $this->assertEquals('Mary', $result['first_name']);
        $this->assertEquals('Jane Smith', $result['last_name']);
    }
    
    public function testParseFullNameWithSingleName()
    {
        $result = parseFullName('John');
        
        $this->assertEquals('John', $result['first_name']);
        $this->assertNull($result['last_name']);
    }
    
    public function testParseFullNameWithNull()
    {
        $result = parseFullName(null);
        
        $this->assertNull($result['first_name']);
        $this->assertNull($result['last_name']);
    }
    
    public function testParseFullNameWithEmptyString()
    {
        $result = parseFullName('');
        
        $this->assertNull($result['first_name']);
        $this->assertNull($result['last_name']);
    }
    
    public function testParseFullNameTrimsWhitespace()
    {
        $result = parseFullName('  John   Doe  ');
        
        $this->assertEquals('John', $result['first_name']);
        $this->assertEquals('Doe', $result['last_name']);
    }
    
    // ==================== sanitizeMoneyValue Tests ====================
    
    public function testSanitizeMoneyValueWithDollarSign()
    {
        $result = sanitizeMoneyValue('$150.00');
        
        $this->assertEquals('150.00', $result);
    }
    
    public function testSanitizeMoneyValueWithCommas()
    {
        $result = sanitizeMoneyValue('$1,234.56');
        
        $this->assertEquals('1234.56', $result);
    }
    
    public function testSanitizeMoneyValueWithBothDollarAndCommas()
    {
        $result = sanitizeMoneyValue('$12,345.67');
        
        $this->assertEquals('12345.67', $result);
    }
    
    public function testSanitizeMoneyValueWithPlainNumber()
    {
        $result = sanitizeMoneyValue('150.00');
        
        $this->assertEquals('150.00', $result);
    }
    
    public function testSanitizeMoneyValueWithNull()
    {
        $result = sanitizeMoneyValue(null);
        
        $this->assertNull($result);
    }
    
    public function testSanitizeMoneyValueWithEmptyString()
    {
        $result = sanitizeMoneyValue('');
        
        $this->assertNull($result);
    }
    
    public function testSanitizeMoneyValueWithOnlySpecialCharacters()
    {
        $result = sanitizeMoneyValue('$,');
        
        $this->assertNull($result);
    }
    
    // ==================== insertSignPermitApplication Tests ====================
    
    public function testInsertSignPermitApplicationWithPrepareFailure()
    {
        $fakeConn = new FakeMysqliConnection(false, 'Prepare error message');
        
        $formData = extractSignPermitFormData([]);
        $result = insertSignPermitApplication($fakeConn, $formData);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Prepare failed', $result['message']);
        $this->assertStringContainsString('Prepare error message', $result['message']);
        $this->assertNull($result['form_id']);
    }
    
    // ==================== Sign Permit Integration Tests ====================
    
    public function testSignPermitFullWorkflowWithValidData()
    {
        $post = [
            'business_name' => 'Test Business',
            'property_owner' => 'John Doe',
            'property_owner_address' => '123 Main St',
            'property_owner_city' => 'Murray',
            'property_owner_state_code' => 'KY',
            'property_owner_zip_code' => '42071',
            'business_address' => '456 Business Rd',
            'business_city' => 'Murray',
            'business_state_code' => 'KY',
            'business_zip_code' => '42071',
            'sign_type_freestanding' => '1',
            'square_footage' => '25.5',
            'total_permit_fee' => '$150.00',
        ];
        
        $formData = extractSignPermitFormData($post);
        $errors = validateSignPermitData($formData);
        
        $this->assertEmpty($errors);
        $this->assertEquals('Test Business', $formData['p_sp_business_name']);
        $this->assertEquals('John', $formData['p_sp_owner_first_name']);
        $this->assertEquals('150.00', $formData['p_sp_permit_fee']);
        $this->assertEquals(25.5, $formData['p_sign_square_footage']);
    }
    
    public function testSignPermitFullWorkflowWithInvalidData()
    {
        $post = [
            'business_name' => '',
            'property_owner' => '',
            'property_owner_zip_code' => 'INVALID',
        ];
        
        $formData = extractSignPermitFormData($post);
        $errors = validateSignPermitData($formData);
        
        $this->assertNotEmpty($errors);
        $this->assertGreaterThan(2, count($errors));
    }

        // ==================== Open Records Request Tests ====================
    
    // ==================== extractOpenRecordsRequestFormData Tests ====================
    
    public function testExtractOpenRecordsRequestFormDataWithFullData()
    {
        $post = [
            'p_form_datetime_resolved' => '2024-01-15 10:30:00',
            'p_orr_commercial_purpose' => 'NO',
            'p_orr_request_for_copies' => 'YES',
            'p_orr_received_on_datetime' => '2024-01-15 09:00:00',
            'p_orr_receivable_datetime' => '2024-01-20 09:00:00',
            'p_orr_denied_reasons' => null,
            'p_orr_applicant_name' => 'John Doe',
            'p_orr_applicant_telephone' => '(555) 123-4567',
            'p_orr_applicant_street' => '123 Main St',
            'p_orr_applicant_city' => 'Murray',
            'p_orr_state_code' => 'KY',
            'p_orr_applicant_zip_code' => '42071',
            'p_orr_records_requested' => 'Zoning records for 123 Main St',
        ];
        
        $result = extractOpenRecordsRequestFormData($post);
        
        $this->assertEquals('2024-01-15 10:30:00', $result['p_form_datetime_resolved']);
        $this->assertEquals(0, $result['p_form_paid_bool']);
        $this->assertNull($result['p_correction_form_id']);
        $this->assertEquals('NO', $result['p_orr_commercial_purpose']);
        $this->assertEquals('YES', $result['p_orr_request_for_copies']);
        $this->assertEquals('John', $result['p_orr_applicant_first_name']);
        $this->assertEquals('Doe', $result['p_orr_applicant_last_name']);
        $this->assertEquals('5551234567', $result['p_orr_applicant_telephone']);
        $this->assertEquals('Zoning records for 123 Main St', $result['p_orr_records_requested']);
    }
    
    public function testExtractOpenRecordsRequestFormDataWithMinimalData()
    {
        $post = [
            'p_orr_applicant_name' => 'Jane Smith',
            'p_orr_records_requested' => 'Building permits',
        ];
        
        $result = extractOpenRecordsRequestFormData($post);
        
        $this->assertEquals('Jane', $result['p_orr_applicant_first_name']);
        $this->assertEquals('Smith', $result['p_orr_applicant_last_name']);
        $this->assertEquals('Building permits', $result['p_orr_records_requested']);
        $this->assertEquals(0, $result['p_form_paid_bool']);
        $this->assertNull($result['p_correction_form_id']);
    }
    
    public function testExtractOpenRecordsRequestFormDataAlwaysSetsFormPaidBoolToZero()
    {
        $post = [];
        $result = extractOpenRecordsRequestFormData($post);
        
        $this->assertEquals(0, $result['p_form_paid_bool']);
    }
    
    public function testExtractOpenRecordsRequestFormDataSanitizesPhoneNumber()
    {
        $post = [
            'p_orr_applicant_telephone' => '(555) 123-4567',
        ];
        
        $result = extractOpenRecordsRequestFormData($post);
        
        $this->assertEquals('5551234567', $result['p_orr_applicant_telephone']);
    }
    
    // ==================== validateOpenRecordsRequestData Tests ====================
    
    public function testValidateOpenRecordsRequestDataWithValidData()
    {
        $formData = [
            'p_orr_applicant_first_name' => 'John',
            'p_orr_applicant_last_name' => 'Doe',
            'p_orr_records_requested' => 'Zoning records',
            'p_orr_commercial_purpose' => 'NO',
            'p_orr_request_for_copies' => 'YES',
            'p_orr_applicant_zip_code' => '42071',
            'p_orr_applicant_telephone' => '5551234567',
        ];
        
        $errors = validateOpenRecordsRequestData($formData);
        
        $this->assertEmpty($errors);
    }
    
    public function testValidateOpenRecordsRequestDataWithMissingRequiredFields()
    {
        $formData = [
            'p_orr_applicant_first_name' => '',
            'p_orr_records_requested' => null,
        ];
        
        $errors = validateOpenRecordsRequestData($formData);
        
        $this->assertNotEmpty($errors);
        $this->assertContains('Applicant name is required', $errors);
        $this->assertContains('Records requested description is required', $errors);
    }
    
    public function testValidateOpenRecordsRequestDataWithInvalidZipCode()
    {
        $formData = [
            'p_orr_applicant_first_name' => 'John',
            'p_orr_records_requested' => 'Records',
            'p_orr_applicant_zip_code' => 'ABCDE',
        ];
        
        $errors = validateOpenRecordsRequestData($formData);
        
        $this->assertContains('ZIP code must be in format 12345 or 12345-6789', $errors);
    }
    
    public function testValidateOpenRecordsRequestDataWithValidZipCodeFormats()
    {
        $formData1 = [
            'p_orr_applicant_first_name' => 'John',
            'p_orr_records_requested' => 'Records',
            'p_orr_applicant_zip_code' => '42071',
        ];
        
        $errors1 = validateOpenRecordsRequestData($formData1);
        $this->assertEmpty($errors1);
        
        $formData2 = [
            'p_orr_applicant_first_name' => 'John',
            'p_orr_records_requested' => 'Records',
            'p_orr_applicant_zip_code' => '42071-1234',
        ];
        
        $errors2 = validateOpenRecordsRequestData($formData2);
        $this->assertEmpty($errors2);
    }
    
    public function testValidateOpenRecordsRequestDataWithInvalidPhoneNumber()
    {
        $formData = [
            'p_orr_applicant_first_name' => 'John',
            'p_orr_records_requested' => 'Records',
            'p_orr_applicant_telephone' => 'not-a-phone',
        ];
        
        $errors = validateOpenRecordsRequestData($formData);
        
        $this->assertContains('Telephone number format is invalid', $errors);
    }
    
    public function testValidateOpenRecordsRequestDataWithValidPhoneNumber()
    {
        $formData = [
            'p_orr_applicant_first_name' => 'John',
            'p_orr_records_requested' => 'Records',
            'p_orr_applicant_telephone' => '(555) 123-4567',
        ];
        
        $errors = validateOpenRecordsRequestData($formData);
        
        $this->assertEmpty($errors);
    }
    
    public function testValidateOpenRecordsRequestDataWithInvalidCommercialPurpose()
    {
        $formData = [
            'p_orr_applicant_first_name' => 'John',
            'p_orr_records_requested' => 'Records',
            'p_orr_commercial_purpose' => 'MAYBE',
        ];
        
        $errors = validateOpenRecordsRequestData($formData);
        
        $this->assertContains('Commercial purpose must be YES or NO', $errors);
    }
    
    public function testValidateOpenRecordsRequestDataWithInvalidRequestForCopies()
    {
        $formData = [
            'p_orr_applicant_first_name' => 'John',
            'p_orr_records_requested' => 'Records',
            'p_orr_request_for_copies' => 'MAYBE',
        ];
        
        $errors = validateOpenRecordsRequestData($formData);
        
        $this->assertContains('Request for copies must be YES or NO', $errors);
    }
    
    // ==================== insertOpenRecordsRequest Tests ====================
    
    public function testInsertOpenRecordsRequestWithPrepareFailure()
    {
        $fakeConn = new FakeMysqliConnection(false, 'Prepare error message');
        
        $formData = extractOpenRecordsRequestFormData([]);
        $result = insertOpenRecordsRequest($fakeConn, $formData);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Prepare failed', $result['message']);
        $this->assertStringContainsString('Prepare error message', $result['message']);
        $this->assertNull($result['form_id']);
    }
    
    // ==================== Open Records Request Integration Tests ====================
    
    public function testOpenRecordsRequestFullWorkflowWithValidData()
    {
        $post = [
            'p_orr_applicant_name' => 'John Doe',
            'p_orr_records_requested' => 'Zoning records for property',
            'p_orr_commercial_purpose' => 'NO',
            'p_orr_request_for_copies' => 'YES',
            'p_orr_applicant_telephone' => '(555) 123-4567',
            'p_orr_applicant_zip_code' => '42071',
        ];
        
        $formData = extractOpenRecordsRequestFormData($post);
        $errors = validateOpenRecordsRequestData($formData);
        
        $this->assertEmpty($errors);
        $this->assertEquals('John', $formData['p_orr_applicant_first_name']);
        $this->assertEquals('Doe', $formData['p_orr_applicant_last_name']);
        $this->assertEquals('NO', $formData['p_orr_commercial_purpose']);
        $this->assertEquals('5551234567', $formData['p_orr_applicant_telephone']);
    }
    
    public function testOpenRecordsRequestFullWorkflowWithInvalidData()
    {
        $post = [
            'p_orr_applicant_name' => '',
            'p_orr_records_requested' => '',
            'p_orr_applicant_zip_code' => 'INVALID',
            'p_orr_commercial_purpose' => 'MAYBE',
        ];
        
        $formData = extractOpenRecordsRequestFormData($post);
        $errors = validateOpenRecordsRequestData($formData);
        
        $this->assertNotEmpty($errors);
        $this->assertGreaterThan(2, count($errors));
    }
    
public function testExtractMinorSubdivisionPlatFormDataFull()
{
    $post = [
        'owner_name' => 'Sarah Smith',
        'surveyor_name' => 'Mark Surveyor',
        'engineer_name' => 'Ed Engineer',
        'applicant_name' => 'Main Applicant'
    ];

    $result = extractMinorSubdivisionPlatFormData($post);

    // Owner name split correctly
    $this->assertEquals('Sarah', $result['owner_first_name']);
    $this->assertEquals('Smith', $result['owner_last_name']);

    // Surveyor name split correctly
    $this->assertEquals('Mark', $result['surveyor_first_name']);
    $this->assertEquals('Surveyor', $result['surveyor_last_name']);

    // Engineer name split correctly
    $this->assertEquals('Ed', $result['engineer_first_name']);
    $this->assertEquals('Engineer', $result['engineer_last_name']);

    // Applicant name stored
    $this->assertEquals('Main Applicant', $result['applicant_name']);
}

public function testExtractMinorSubdivisionPlatFormDataMissing()
{
    $result = extractMinorSubdivisionPlatFormData([]);

    $this->assertNull($result['owner_name'] ?? null);
    $this->assertNull($result['plat_description'] ?? null);
}

public function testValidateMinorSubdivisionPlatDataValid()
{
    $data = [
        'applicant_name' => 'John Doe',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2025-01-01'
    ];

    $errors = validateMinorSubdivisionPlatData($data);

    $this->assertIsArray($errors);
    $this->assertCount(0, $errors); // No errors means valid
}

public function testValidateMinorSubdivisionPlatDataMissingRequired()
{
    $data = []; // Missing required fields

    $errors = validateMinorSubdivisionPlatData($data);

    $this->assertIsArray($errors);
    $this->assertNotEmpty($errors); // Validation should fail
    $this->assertGreaterThanOrEqual(1, count($errors));
}

public function testUploadFileToDirectoryMovesFile()
{
    $tmp = tempnam(sys_get_temp_dir(), 'upl');
    file_put_contents($tmp, "TESTDATA");

    $files = [
        'file_field' => [
            'name' => 'doc.pdf',
            'tmp_name' => $tmp,
            'error' => 0
        ]
    ];

    $targetDir = sys_get_temp_dir() . '/uploads/';
    @mkdir($targetDir);

    $filename = uploadFileToDirectory($files, 'file_field', $targetDir);

    $this->assertFileExists($targetDir . $filename);
}

public function testInsertMinorSubdivisionPlatApplicationPrepareFailure()
{
    $fakeConn = new FakeMysqliConnection(null, null);

    $result = insertMinorSubdivisionPlatApplication($fakeConn, []);

    $this->assertFalse($result['success']);
    $this->assertNull($result['form_id']); // <- CORRECT KEY
}

// ==================== Major Subdivision Plat Application Tests ====================
    
    public function testExtractMajorSubdivisionPlatFormDataWithFullData()
    {
        $post = [
            'application_filing_date' => '2024-01-15',
            'technical_review_date' => '2024-02-01',
            'preliminary_approval_date' => '2024-03-01',
            'final_approval_date' => '2024-04-01',
            'applicant_name' => 'Major Dev LLC',
            'officers_names' => ['John Doe', 'Jane Smith'],
            'surveyor_name' => 'Robert Johnson',
            'engineer_name' => 'Mary Williams',
            'owner_first_name' => 'Sarah',
            'owner_last_name' => 'Smith',
            'property_street' => '123 Main St',
            'parcel_number' => '12345',
            'acreage' => '50.5',
            'current_zoning' => 'R-1',
            'checklist_application' => '1',
            'checklist_construction_plans' => '1',
            'checklist_traffic_study' => '1',
        ];
        
        $result = extractMajorSubdivisionPlatFormData($post);
        
        $this->assertEquals('2024-01-15', $result['application_filing_date']);
        $this->assertEquals('2024-02-01', $result['technical_review_date']);
        $this->assertEquals('Major Dev LLC', $result['applicant_name']);
        $this->assertEquals('Robert', $result['surveyor_first_name']);
        $this->assertEquals('Johnson', $result['surveyor_last_name']);
        $this->assertEquals('Mary', $result['engineer_first_name']);
        $this->assertEquals('Williams', $result['engineer_last_name']);
        $this->assertEquals('Sarah', $result['owner_first_name']);
        $this->assertEquals('Smith', $result['owner_last_name']);
        $this->assertEquals(1, $result['checklist_application']);
        $this->assertEquals(1, $result['checklist_construction_plans']);
        $this->assertEquals(1, $result['checklist_traffic_study']);
        $this->assertEquals(0, $result['checklist_drainage']); // Not checked
    }
    
    public function testExtractMajorSubdivisionPlatFormDataWithAdditionalApplicantOfficers()
    {
        $post = [
            'additional_applicant_officers_1' => ['Officer A', 'Officer B'],
            'additional_applicant_officers_2' => ['Officer C'],
        ];
        
        $result = extractMajorSubdivisionPlatFormData($post);
        
        $this->assertNotNull($result['additional_applicant_officers']);
        $decoded = json_decode($result['additional_applicant_officers'], true);
        $this->assertArrayHasKey('1', $decoded);
        $this->assertArrayHasKey('2', $decoded);
        $this->assertContains('Officer A', $decoded['1']);
        $this->assertContains('Officer C', $decoded['2']);
    }
    
    public function testExtractMajorSubdivisionPlatFormDataWithAllChecklistItems()
    {
        $post = [
            'checklist_application' => '1',
            'checklist_agency_signatures' => '1',
            'checklist_lot_layout' => '1',
            'checklist_topographic' => '1',
            'checklist_restrictions' => '1',
            'checklist_fees' => '1',
            'checklist_construction_plans' => '1',
            'checklist_traffic_study' => '1',
            'checklist_drainage' => '1',
            'checklist_pavement' => '1',
            'checklist_swppp' => '1',
            'checklist_bond_estimate' => '1',
            'checklist_construction_contract' => '1',
            'checklist_construction_bond' => '1',
            'checklist_notice_proceed' => '1',
        ];
        
        $result = extractMajorSubdivisionPlatFormData($post);
        
        // Verify all 15 checklist items
        $this->assertEquals(1, $result['checklist_application']);
        $this->assertEquals(1, $result['checklist_agency_signatures']);
        $this->assertEquals(1, $result['checklist_lot_layout']);
        $this->assertEquals(1, $result['checklist_topographic']);
        $this->assertEquals(1, $result['checklist_restrictions']);
        $this->assertEquals(1, $result['checklist_fees']);
        $this->assertEquals(1, $result['checklist_construction_plans']);
        $this->assertEquals(1, $result['checklist_traffic_study']);
        $this->assertEquals(1, $result['checklist_drainage']);
        $this->assertEquals(1, $result['checklist_pavement']);
        $this->assertEquals(1, $result['checklist_swppp']);
        $this->assertEquals(1, $result['checklist_bond_estimate']);
        $this->assertEquals(1, $result['checklist_construction_contract']);
        $this->assertEquals(1, $result['checklist_construction_bond']);
        $this->assertEquals(1, $result['checklist_notice_proceed']);
    }
    
    public function testExtractMajorSubdivisionPlatFormDataParsesNames()
    {
        $post = [
            'surveyor_name' => 'John Surveyor',
            'engineer_name' => 'Jane Engineer',
        ];
        
        $result = extractMajorSubdivisionPlatFormData($post);
        
        $this->assertEquals('John', $result['surveyor_first_name']);
        $this->assertEquals('Surveyor', $result['surveyor_last_name']);
        $this->assertEquals('Jane', $result['engineer_first_name']);
        $this->assertEquals('Engineer', $result['engineer_last_name']);
    }
    
    public function testExtractMajorSubdivisionPlatFormDataWithMinimalData()
    {
        $post = [];
        
        $result = extractMajorSubdivisionPlatFormData($post);
        
        // Should return array with null values
        $this->assertIsArray($result);
        $this->assertNull($result['applicant_name']);
        $this->assertNull($result['surveyor_first_name']);
        $this->assertEquals(0, $result['checklist_application']);
    }
    
    public function testValidateMajorSubdivisionPlatDataWithValidData()
    {
        $formData = [
            'applicant_name' => 'Major Dev LLC',
            'signature_name_1' => 'John Doe',
            'signature_date_1' => '2024-01-15',
            'applicant_email' => 'john@example.com',
            'applicant_zip_code' => '42071',
        ];
        
        $errors = validateMajorSubdivisionPlatData($formData);
        
        $this->assertEmpty($errors);
    }
    
    public function testValidateMajorSubdivisionPlatDataWithMissingRequired()
    {
        $formData = [];
        
        $errors = validateMajorSubdivisionPlatData($formData);
        
        $this->assertNotEmpty($errors);
        $this->assertGreaterThanOrEqual(1, count($errors));
    }
    
    public function testInsertMajorSubdivisionPlatApplicationWithPrepareFailure()
    {
        $fakeConn = new FakeMysqliConnection(false, 'Prepare error message');
        
        $formData = extractMajorSubdivisionPlatFormData([]);
        $result = insertMajorSubdivisionPlatApplication($fakeConn, $formData);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Prepare failed', $result['message']);
        $this->assertNull($result['form_id']);
    }
    
    public function testMajorSubdivisionFullWorkflowWithValidData()
    {
        $post = [
            'applicant_name' => 'Major Dev LLC',
            'surveyor_name' => 'John Surveyor',
            'engineer_name' => 'Jane Engineer',
            'signature_name_1' => 'John Doe',
            'signature_date_1' => '2024-01-15',
            'property_street' => '123 Main St',
            'acreage' => '50.5',
            'checklist_application' => '1',
        ];
        
        $formData = extractMajorSubdivisionPlatFormData($post);
        $errors = validateMajorSubdivisionPlatData($formData);
        
        $this->assertEmpty($errors);
        $this->assertEquals('Major Dev LLC', $formData['applicant_name']);
        $this->assertEquals('John', $formData['surveyor_first_name']);
        $this->assertEquals('Jane', $formData['engineer_first_name']);
        $this->assertEquals(1, $formData['checklist_application']);
    }

    public function testExtractGeneralDevelopmentPlanFormDataWithFullData()
{
    $post = [
        'docket_number' => 'GDP-2024-001',
        'public_hearing_date' => '2024-03-15',
        'date_application_filed' => '2024-02-01',
        'pre_application_meeting_date' => '2024-01-15',
        'applicant_name' => 'Test Development LLC',
        'officers_names' => ['John Doe', 'Jane Smith'],
        'applicant_street' => '123 Main St',
        'applicant_phone' => '555-1234',
        'applicant_cell' => '555-5678',
        'applicant_city' => 'Danville',
        'applicant_state' => 'KY',
        'applicant_zip_code' => '40423',
        'applicant_email' => 'test@example.com',
        'applicant_first_name' => 'John',
        'applicant_last_name' => 'Owner',
        'owner_street' => '456 Oak Ave',
        'owner_phone' => '555-9999',
        'attorney_first_name' => 'Bob',
        'attorney_last_name' => 'Attorney',
        'law_firm' => 'Law Firm LLP',
        'property_street' => '789 Property Ln',
        'property_city' => 'Danville',
        'property_state' => 'KY',
        'property_zip_code' => '40423',
        'parcel_number' => '12345',
        'acreage' => '25.5',
        'current_zoning' => 'R-1',
        'gdp_amendment_request' => 'Request to amend GDP for mixed use',
        'proposed_conditions' => 'Condition 1, Condition 2',
        'finding_type' => 'significant_change',
        'findings_explanation' => 'Detailed explanation of findings',
        'checklist_application' => '1',
        'checklist_adjacent' => '1',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
    ];
    
    $result = extractGeneralDevelopmentPlanFormData($post);
    
    $this->assertEquals('GDP-2024-001', $result['docket_number']);
    $this->assertEquals('Test Development LLC', $result['applicant_name']);
    $this->assertEquals('John', $result['owner_first_name']);
    $this->assertEquals('Owner', $result['owner_last_name']);
    $this->assertEquals('Bob', $result['attorney_first_name']);
    $this->assertEquals('Attorney', $result['attorney_last_name']);
    $this->assertEquals('significant_change', $result['finding_type']);
    $this->assertEquals(1, $result['checklist_application']);
    $this->assertEquals(1, $result['checklist_adjacent']);
    $this->assertEquals(0, $result['checklist_fees']); // Not checked
}

public function testExtractGeneralDevelopmentPlanFormDataWithAdditionalApplicantOfficers()
{
    $post = [
        'additional_applicant_officers_1' => ['Officer A', 'Officer B'],
        'additional_applicant_officers_2' => ['Officer C'],
    ];
    
    $result = extractGeneralDevelopmentPlanFormData($post);
    
    $this->assertNotNull($result['additional_applicant_officers']);
    $decoded = json_decode($result['additional_applicant_officers'], true);
    $this->assertArrayHasKey('1', $decoded);
    $this->assertArrayHasKey('2', $decoded);
    $this->assertContains('Officer A', $decoded['1']);
    $this->assertContains('Officer C', $decoded['2']);
}

public function testExtractGeneralDevelopmentPlanFormDataWithMinimalData()
{
    $post = [];
    
    $result = extractGeneralDevelopmentPlanFormData($post);
    
    $this->assertIsArray($result);
    $this->assertNull($result['applicant_name']);
    $this->assertNull($result['docket_number']);
    $this->assertEquals(0, $result['checklist_application']);
}

public function testExtractGeneralDevelopmentPlanFormDataParsesAttorneyName()
{
    $post = [
        'attorney_name' => 'Robert Attorney',
    ];
    
    $result = extractGeneralDevelopmentPlanFormData($post);
    
    $this->assertEquals('Robert', $result['attorney_first_name']);
    $this->assertEquals('Attorney', $result['attorney_last_name']);
}

public function testExtractGeneralDevelopmentPlanFormDataWithAllChecklistItems()
{
    $post = [
        'checklist_application' => '1',
        'checklist_adjacent' => '1',
        'checklist_verification' => '1',
        'checklist_fees' => '1',
        'checklist_gdp_conditions' => '1',
        'checklist_concept' => '1',
        'checklist_traffic' => '1',
        'checklist_geologic' => '1',
    ];
    
    $result = extractGeneralDevelopmentPlanFormData($post);
    
    $this->assertEquals(1, $result['checklist_application']);
    $this->assertEquals(1, $result['checklist_adjacent']);
    $this->assertEquals(1, $result['checklist_verification']);
    $this->assertEquals(1, $result['checklist_fees']);
    $this->assertEquals(1, $result['checklist_gdp_conditions']);
    $this->assertEquals(1, $result['checklist_concept']);
    $this->assertEquals(1, $result['checklist_traffic']);
    $this->assertEquals(1, $result['checklist_geologic']);
}

public function testValidateGeneralDevelopmentPlanDataWithValidData()
{
    $formData = [
        'applicant_name' => 'Test Development LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_email' => 'test@example.com',
        'applicant_zip_code' => '40423',
        'finding_type' => 'significant_change',
    ];
    
    $errors = validateGeneralDevelopmentPlanData($formData);
    
    $this->assertEmpty($errors);
}

public function testValidateGeneralDevelopmentPlanDataWithMissingRequired()
{
    $formData = [];
    
    $errors = validateGeneralDevelopmentPlanData($formData);
    
    $this->assertNotEmpty($errors);
    $this->assertContains('Applicant name is required', $errors);
    $this->assertContains('At least one signature is required', $errors);
    $this->assertContains('At least one signature date is required', $errors);
}

public function testValidateGeneralDevelopmentPlanDataWithInvalidZipCode()
{
    $formData = [
        'applicant_name' => 'Test Development LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_zip_code' => 'INVALID',
    ];
    
    $errors = validateGeneralDevelopmentPlanData($formData);
    
    $this->assertContains('Applicant ZIP code must be in format 12345 or 12345-6789', $errors);
}

public function testValidateGeneralDevelopmentPlanDataWithInvalidPhone()
{
    $formData = [
        'applicant_name' => 'Test Development LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_phone' => 'not-a-phone',
    ];
    
    $errors = validateGeneralDevelopmentPlanData($formData);
    
    $this->assertContains('Applicant phone format is invalid', $errors);
}

public function testValidateGeneralDevelopmentPlanDataWithInvalidEmail()
{
    $formData = [
        'applicant_name' => 'Test Development LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_email' => 'invalid-email',
    ];
    
    $errors = validateGeneralDevelopmentPlanData($formData);
    
    $this->assertContains('Applicant email format is invalid', $errors);
}

public function testValidateGeneralDevelopmentPlanDataWithInvalidFindingType()
{
    $formData = [
        'applicant_name' => 'Test Development LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'finding_type' => 'invalid_finding',
    ];
    
    $errors = validateGeneralDevelopmentPlanData($formData);
    
    $this->assertContains('Finding type must be one of: significant_change, physical_development, petition_movement', $errors);
}

public function testValidateGeneralDevelopmentPlanDataWithValidFindingTypes()
{
    $validTypes = ['significant_change', 'physical_development', 'petition_movement'];
    
    foreach ($validTypes as $type) {
        $formData = [
            'applicant_name' => 'Test Development LLC',
            'signature_name_1' => 'John Doe',
            'signature_date_1' => '2024-02-01',
            'finding_type' => $type,
        ];
        
        $errors = validateGeneralDevelopmentPlanData($formData);
        
        $this->assertEmpty($errors);
    }
}

public function testInsertGeneralDevelopmentPlanApplicationWithPrepareFailure()
{
    $fakeConn = new FakeMysqliConnection(false, 'Prepare error message');
    
    $formData = extractGeneralDevelopmentPlanFormData([]);
    $result = insertGeneralDevelopmentPlanApplication($fakeConn, $formData);
    
    $this->assertFalse($result['success']);
    $this->assertStringContainsString('Prepare failed', $result['message']);
    $this->assertNull($result['form_id']);
}

public function testGeneralDevelopmentPlanFullWorkflowWithValidData()
{
    $post = [
        'applicant_name' => 'Test Development LLC',
        'officers_names' => ['John Doe', 'Jane Smith'],
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_email' => 'test@example.com',
        'applicant_zip_code' => '40423',
        'gdp_amendment_request' => 'Request to amend GDP',
        'finding_type' => 'significant_change',
        'checklist_application' => '1',
    ];
    
    $formData = extractGeneralDevelopmentPlanFormData($post);
    $errors = validateGeneralDevelopmentPlanData($formData);
    
    $this->assertEmpty($errors);
    $this->assertEquals('Test Development LLC', $formData['applicant_name']);
    $this->assertEquals('significant_change', $formData['finding_type']);
    $this->assertEquals(1, $formData['checklist_application']);
}

public function testGeneralDevelopmentPlanFullWorkflowWithInvalidData()
{
    $post = [
        'applicant_name' => '',
        'applicant_zip_code' => 'INVALID',
        'applicant_email' => 'invalid-email',
        'finding_type' => 'wrong_type',
    ];
    
    $formData = extractGeneralDevelopmentPlanFormData($post);
    $errors = validateGeneralDevelopmentPlanData($formData);
    
    $this->assertNotEmpty($errors);
    $this->assertGreaterThan(3, count($errors));
}

/**
 * Tests for Future Land Use Map Application Functions
 * Add these tests to your existing php_functions_Test.php file
 */

// ==================== extractFutureLandUseMapFormData Tests ====================

public function testExtractFutureLandUseMapFormDataWithFullData()
{
    $post = [
        'docket_number' => 'FLUM-2024-001',
        'public_hearing_date' => '2024-03-15',
        'date_application_filed' => '2024-02-01',
        'pre_application_meeting_date' => '2024-01-15',
        'applicant_name' => 'Test FLUM LLC',
        'officers_names' => ['John Doe', 'Jane Smith'],
        'applicant_street' => '123 Main St',
        'applicant_phone' => '555-1234',
        'applicant_cell' => '555-5678',
        'applicant_city' => 'Springfield',
        'applicant_state' => 'KY',
        'applicant_zip_code' => '40423',
        'applicant_email' => 'test@example.com',
        'applicant_first_name' => 'John',
        'applicant_last_name' => 'Owner',
        'owner_street' => '456 Oak Ave',
        'owner_city' => 'Springfield',
        'owner_state' => 'KY',
        'owner_zip_code' => '40423',
        'attorney_first_name' => 'Bob',
        'attorney_last_name' => 'Attorney',
        'law_firm' => 'Law Firm LLC',
        'property_street' => '789 Elm St',
        'property_city' => 'Springfield',
        'property_state' => 'KY',
        'property_zip_code' => '40423',
        'parcel_number' => '12345',
        'acreage' => '5.5',
        'current_zoning' => 'R-1',
        'current_designation' => 'Residential',
        'proposed_designation' => 'Commercial',
        'designation_reason' => 'Economic development',
        'flum_request' => 'Detailed request description',
        'finding_type' => 'public_benefit',
        'findings_explanation' => 'Explanation here',
        'checklist_application' => '1',
        'checklist_exhibit' => '1',
        'signature_date_1' => '2024-02-15',
        'signature_name_1' => 'John Doe',
    ];
    
    $result = extractFutureLandUseMapFormData($post);
    
    $this->assertEquals('FLUM-2024-001', $result['docket_number']);
    $this->assertEquals('Test FLUM LLC', $result['applicant_name']);
    $this->assertEquals('John', $result['owner_first_name']);
    $this->assertEquals('Owner', $result['owner_last_name']);
    $this->assertEquals('Bob', $result['attorney_first_name']);
    $this->assertEquals('Attorney', $result['attorney_last_name']);
    $this->assertEquals('public_benefit', $result['finding_type']);
    $this->assertEquals(1, $result['checklist_application']);
    $this->assertEquals(1, $result['checklist_exhibit']);
    $this->assertEquals(0, $result['checklist_concept']); // Not checked
}

public function testExtractFutureLandUseMapFormDataWithAdditionalApplicantOfficers()
{
    $post = [
        'additional_applicant_officers_1' => ['Officer A', 'Officer B'],
        'additional_applicant_officers_2' => ['Officer C'],
    ];
    
    $result = extractFutureLandUseMapFormData($post);
    
    $this->assertNotNull($result['additional_applicant_officers']);
    $decoded = json_decode($result['additional_applicant_officers'], true);
    $this->assertArrayHasKey('1', $decoded);
    $this->assertArrayHasKey('2', $decoded);
    $this->assertContains('Officer A', $decoded['1']);
    $this->assertContains('Officer C', $decoded['2']);
}

public function testExtractFutureLandUseMapFormDataWithMinimalData()
{
    $post = [];
    
    $result = extractFutureLandUseMapFormData($post);
    
    $this->assertIsArray($result);
    $this->assertNull($result['applicant_name']);
    $this->assertNull($result['docket_number']);
    $this->assertEquals(0, $result['checklist_application']);
}

public function testExtractFutureLandUseMapFormDataWithAllChecklistItems()
{
    $post = [
        'checklist_application' => '1',
        'checklist_exhibit' => '1',
        'checklist_concept' => '1',
        'checklist_compatibility' => '1',
    ];
    
    $result = extractFutureLandUseMapFormData($post);
    
    $this->assertEquals(1, $result['checklist_application']);
    $this->assertEquals(1, $result['checklist_exhibit']);
    $this->assertEquals(1, $result['checklist_concept']);
    $this->assertEquals(1, $result['checklist_compatibility']);
}

public function testExtractFutureLandUseMapFormDataParsesParcelNumberAsInt()
{
    $post = [
        'parcel_number' => '12345',
    ];
    
    $result = extractFutureLandUseMapFormData($post);
    
    $this->assertIsInt($result['parcel_number']);
    $this->assertEquals(12345, $result['parcel_number']);
}

public function testExtractFutureLandUseMapFormDataHandlesEmptyParcelNumber()
{
    $post = [
        'parcel_number' => '',
    ];
    
    $result = extractFutureLandUseMapFormData($post);
    
    $this->assertNull($result['parcel_number']);
}

// ==================== validateFutureLandUseMapData Tests ====================

public function testValidateFutureLandUseMapDataWithValidData()
{
    $formData = [
        'applicant_name' => 'Test FLUM LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_email' => 'test@example.com',
        'applicant_zip_code' => '40423',
        'finding_type' => 'public_benefit',
    ];
    
    $errors = validateFutureLandUseMapData($formData);
    
    $this->assertEmpty($errors);
}

public function testValidateFutureLandUseMapDataWithMissingRequired()
{
    $formData = [];
    
    $errors = validateFutureLandUseMapData($formData);
    
    $this->assertNotEmpty($errors);
    $this->assertContains('Applicant name is required', $errors);
    $this->assertContains('At least one signature is required', $errors);
    $this->assertContains('At least one signature date is required', $errors);
}

public function testValidateFutureLandUseMapDataWithInvalidZipCode()
{
    $formData = [
        'applicant_name' => 'Test FLUM LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_zip_code' => 'INVALID',
    ];
    
    $errors = validateFutureLandUseMapData($formData);
    
    $this->assertContains('Applicant ZIP code must be in format 12345 or 12345-6789', $errors);
}

public function testValidateFutureLandUseMapDataWithMultipleInvalidZipCodes()
{
    $formData = [
        'applicant_name' => 'Test FLUM LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_zip_code' => 'INVALID',
        'owner_zip_code' => '123',
        'property_zip_code' => 'WRONG',
    ];
    
    $errors = validateFutureLandUseMapData($formData);
    
    $this->assertContains('Applicant ZIP code must be in format 12345 or 12345-6789', $errors);
    $this->assertContains('Owner ZIP code must be in format 12345 or 12345-6789', $errors);
    $this->assertContains('Property ZIP code must be in format 12345 or 12345-6789', $errors);
}

public function testValidateFutureLandUseMapDataWithValidZipCodeFormats()
{
    $formData1 = [
        'applicant_name' => 'Test FLUM LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_zip_code' => '40423',
    ];
    
    $errors1 = validateFutureLandUseMapData($formData1);
    $this->assertEmpty($errors1);
    
    $formData2 = [
        'applicant_name' => 'Test FLUM LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_zip_code' => '40423-1234',
    ];
    
    $errors2 = validateFutureLandUseMapData($formData2);
    $this->assertEmpty($errors2);
}

public function testValidateFutureLandUseMapDataWithInvalidPhone()
{
    $formData = [
        'applicant_name' => 'Test FLUM LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_phone' => 'not-a-phone',
    ];
    
    $errors = validateFutureLandUseMapData($formData);
    
    $this->assertContains('Applicant phone format is invalid', $errors);
}

public function testValidateFutureLandUseMapDataWithValidPhoneFormats()
{
    $validPhones = [
        '555-1234',
        '(555) 123-4567',
        '555.123.4567',
        '5551234567',
        '555 123 4567',
    ];
    
    foreach ($validPhones as $phone) {
        $formData = [
            'applicant_name' => 'Test FLUM LLC',
            'signature_name_1' => 'John Doe',
            'signature_date_1' => '2024-02-01',
            'applicant_phone' => $phone,
        ];
        
        $errors = validateFutureLandUseMapData($formData);
        $this->assertEmpty($errors, "Phone format '$phone' should be valid");
    }
}

public function testValidateFutureLandUseMapDataWithInvalidEmail()
{
    $formData = [
        'applicant_name' => 'Test FLUM LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_email' => 'invalid-email',
    ];
    
    $errors = validateFutureLandUseMapData($formData);
    
    $this->assertContains('Applicant email format is invalid', $errors);
}

public function testValidateFutureLandUseMapDataWithMultipleInvalidEmails()
{
    $formData = [
        'applicant_name' => 'Test FLUM LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_email' => 'invalid',
        'owner_email' => 'also-invalid',
        'attorney_email' => 'wrong@',
    ];
    
    $errors = validateFutureLandUseMapData($formData);
    
    $this->assertContains('Applicant email format is invalid', $errors);
    $this->assertContains('Owner email format is invalid', $errors);
    $this->assertContains('Attorney email format is invalid', $errors);
}

public function testValidateFutureLandUseMapDataWithInvalidFindingType()
{
    $formData = [
        'applicant_name' => 'Test FLUM LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'finding_type' => 'invalid_finding',
    ];
    
    $errors = validateFutureLandUseMapData($formData);
    
    $this->assertContains('Finding type must be one of: public_benefit, inconsistency_correction, clear_compatability', $errors);
}

public function testValidateFutureLandUseMapDataWithValidFindingTypes()
{
    $validTypes = ['public_benefit', 'inconsistency_correction', 'clear_compatability'];
    
    foreach ($validTypes as $type) {
        $formData = [
            'applicant_name' => 'Test FLUM LLC',
            'signature_name_1' => 'John Doe',
            'signature_date_1' => '2024-02-01',
            'finding_type' => $type,
        ];
        
        $errors = validateFutureLandUseMapData($formData);
        
        $this->assertEmpty($errors);
    }
}

public function testValidateFutureLandUseMapDataAcceptsSecondSignature()
{
    $formData = [
        'applicant_name' => 'Test FLUM LLC',
        'signature_name_2' => 'Jane Smith',
        'signature_date_2' => '2024-02-01',
    ];
    
    $errors = validateFutureLandUseMapData($formData);
    
    $this->assertEmpty($errors);
}

// ==================== processFutureLandUseMapFileUploads Tests ====================

public function testProcessFutureLandUseMapFileUploadsWithNoFiles()
{
    $files = [];
    
    $result = processFutureLandUseMapFileUploads($files);
    
    $this->assertIsArray($result);
    $this->assertNull($result['file_exhibit']);
    $this->assertNull($result['file_concept']);
    $this->assertNull($result['file_compatibility']);
}

public function testProcessFutureLandUseMapFileUploadsWithUploadErrors()
{
    $files = [
        'file_exhibit' => ['error' => UPLOAD_ERR_NO_FILE],
        'file_concept' => ['error' => UPLOAD_ERR_INI_SIZE],
        'file_compatibility' => ['error' => UPLOAD_ERR_PARTIAL],
    ];
    
    $result = processFutureLandUseMapFileUploads($files);
    
    $this->assertNull($result['file_exhibit']);
    $this->assertNull($result['file_concept']);
    $this->assertNull($result['file_compatibility']);
}

public function testProcessFutureLandUseMapFileUploadsReturnsCorrectKeys()
{
    $files = [];
    
    $result = processFutureLandUseMapFileUploads($files);
    
    $this->assertArrayHasKey('file_exhibit', $result);
    $this->assertArrayHasKey('file_concept', $result);
    $this->assertArrayHasKey('file_compatibility', $result);
}

// ==================== insertFutureLandUseMapApplication Tests ====================

public function testInsertFutureLandUseMapApplicationWithPrepareFailure()
{
    $fakeConn = new FakeMysqliConnection(false, 'Prepare error message');
    
    $formData = extractFutureLandUseMapFormData([]);
    $result = insertFutureLandUseMapApplication($fakeConn, $formData);
    
    $this->assertFalse($result['success']);
    $this->assertStringContainsString('Prepare failed', $result['message']);
    $this->assertNull($result['form_id']);
}

public function testInsertFutureLandUseMapApplicationReturnsCorrectStructure()
{
    $fakeConn = new FakeMysqliConnection(false, 'Test error');
    
    $formData = extractFutureLandUseMapFormData([]);
    $result = insertFutureLandUseMapApplication($fakeConn, $formData);
    
    $this->assertIsArray($result);
    $this->assertArrayHasKey('success', $result);
    $this->assertArrayHasKey('message', $result);
    $this->assertArrayHasKey('form_id', $result);
}

// ==================== Full Workflow Tests ====================

public function testFutureLandUseMapFullWorkflowWithValidData()
{
    $post = [
        'applicant_name' => 'Test FLUM LLC',
        'officers_names' => ['John Doe', 'Jane Smith'],
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_email' => 'test@example.com',
        'applicant_zip_code' => '40423',
        'flum_request' => 'Request to change designation',
        'finding_type' => 'public_benefit',
        'checklist_application' => '1',
    ];
    
    $formData = extractFutureLandUseMapFormData($post);
    $errors = validateFutureLandUseMapData($formData);
    
    $this->assertEmpty($errors);
    $this->assertEquals('Test FLUM LLC', $formData['applicant_name']);
    $this->assertEquals('public_benefit', $formData['finding_type']);
    $this->assertEquals(1, $formData['checklist_application']);
}

public function testFutureLandUseMapFullWorkflowWithInvalidData()
{
    $post = [
        'applicant_name' => '',
        'applicant_zip_code' => 'INVALID',
        'applicant_email' => 'invalid-email',
        'finding_type' => 'wrong_type',
    ];
    
    $formData = extractFutureLandUseMapFormData($post);
    $errors = validateFutureLandUseMapData($formData);
    
    $this->assertNotEmpty($errors);
    $this->assertGreaterThan(3, count($errors));
}

public function testFutureLandUseMapFullWorkflowWithFilesAndValidation()
{
    $post = [
        'applicant_name' => 'Test FLUM LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'checklist_exhibit' => '1',
        'checklist_compatibility' => '1',
    ];
    
    $files = [
        'file_exhibit' => ['error' => UPLOAD_ERR_NO_FILE],
        'file_compatibility' => ['error' => UPLOAD_ERR_NO_FILE],
    ];
    
    $formData = extractFutureLandUseMapFormData($post, $files);
    $errors = validateFutureLandUseMapData($formData);
    $fileData = processFutureLandUseMapFileUploads($files);
    
    $this->assertEmpty($errors);
    $this->assertNull($fileData['file_exhibit']);
    $this->assertNull($fileData['file_compatibility']);
    $this->assertEquals(1, $formData['checklist_exhibit']);
    $this->assertEquals(1, $formData['checklist_compatibility']);
}

// ==================== extractConditionalUsePermitFormData Tests ====================

public function testExtractConditionalUsePermitFormDataWithFullData()
{
    $post = [
        'docket_number' => 'CUP-2024-001',
        'public_hearing_date' => '2024-03-15',
        'date_application_filed' => '2024-02-01',
        'pre_application_meeting_date' => '2024-01-15',
        'applicant_name' => 'Test CUP LLC',
        'officers_names' => ['John Doe', 'Jane Smith'],
        'applicant_mailing_address' => '123 Main St, Springfield, KY 40423',
        'applicant_phone' => '555-1234',
        'applicant_cell' => '555-5678',
        'applicant_email' => 'test@example.com',
        'owner_name' => 'John Owner',
        'owner_mailing_address' => '456 Oak Ave, Springfield, KY 40423',
        'owner_phone' => '555-9999',
        'owner_email' => 'owner@example.com',
        'attorney_first_name' => 'Bob',
        'attorney_last_name' => 'Attorney',
        'law_firm' => 'Law Firm LLC',
        'attorney_phone' => '555-7777',
        'property_address' => '789 Elm St, Springfield, KY 40423',
        'parcel_number' => '12345',
        'acreage' => '5.5',
        'current_zoning' => 'R-1',
        'cup_request' => 'Request for conditional use permit',
        'proposed_conditions' => 'Proposed conditions here',
        'checklist_application' => '1',
        'checklist_exhibit' => '1',
        'signature_date_1' => '2024-02-15',
        'signature_name_1' => 'John Doe',
        'application_fee' => '150.00',
        'certificate_fee' => '50.00',
        'date_fees_received' => '2024-02-15',
    ];
    
    $files = [
        'file_exhibit' => ['error' => UPLOAD_ERR_OK, 'name' => 'exhibit.pdf'],
        'file_adjacent' => ['error' => UPLOAD_ERR_OK, 'name' => 'adjacent.pdf'],
    ];
    
    $result = extractConditionalUsePermitFormData($post, $files);
    
    $this->assertEquals('CUP-2024-001', $result['docket_number']);
    $this->assertEquals('Test CUP LLC', $result['applicant_name']);
    $this->assertEquals('John Owner', $result['owner_name']);
    $this->assertEquals('Bob', $result['attorney_first_name']);
    $this->assertEquals('Attorney', $result['attorney_last_name']);
    $this->assertEquals(1, $result['checklist_application']);
    $this->assertEquals(1, $result['checklist_exhibit']);
    $this->assertEquals(0, $result['checklist_adjacent']); // Not checked
    $this->assertEquals('exhibit.pdf', $result['file_exhibit']);
    $this->assertEquals('adjacent.pdf', $result['file_adjacent']);
}

public function testExtractConditionalUsePermitFormDataWithAdditionalApplicantOfficers()
{
    $post = [
        'additional_applicant_officers_1' => ['Officer A', 'Officer B'],
        'additional_applicant_officers_2' => ['Officer C'],
    ];
    
    $result = extractConditionalUsePermitFormData($post);
    
    $this->assertNotNull($result['additional_applicant_officers']);
    $decoded = json_decode($result['additional_applicant_officers'], true);
    $this->assertArrayHasKey('1', $decoded);
    $this->assertArrayHasKey('2', $decoded);
    $this->assertContains('Officer A', $decoded['1']);
    $this->assertContains('Officer C', $decoded['2']);
}

public function testExtractConditionalUsePermitFormDataWithMinimalData()
{
    $post = [];
    
    $result = extractConditionalUsePermitFormData($post);
    
    $this->assertIsArray($result);
    $this->assertNull($result['applicant_name']);
    $this->assertNull($result['docket_number']);
    $this->assertEquals(0, $result['checklist_application']);
}

public function testExtractConditionalUsePermitFormDataWithAllChecklistItems()
{
    $post = [
        'checklist_application' => '1',
        'checklist_exhibit' => '1',
        'checklist_adjacent' => '1',
        'checklist_fees' => '1',
    ];
    
    $result = extractConditionalUsePermitFormData($post);
    
    $this->assertEquals(1, $result['checklist_application']);
    $this->assertEquals(1, $result['checklist_exhibit']);
    $this->assertEquals(1, $result['checklist_adjacent']);
    $this->assertEquals(1, $result['checklist_fees']);
}

public function testExtractConditionalUsePermitFormDataParsesParcelNumberAsInt()
{
    $post = [
        'parcel_number' => '12345',
    ];
    
    $result = extractConditionalUsePermitFormData($post);
    
    $this->assertIsInt($result['parcel_number']);
    $this->assertEquals(12345, $result['parcel_number']);
}

public function testExtractConditionalUsePermitFormDataHandlesEmptyParcelNumber()
{
    $post = [
        'parcel_number' => '',
    ];
    
    $result = extractConditionalUsePermitFormData($post);
    
    $this->assertNull($result['parcel_number']);
}

public function testExtractConditionalUsePermitFormDataHandlesCorrectionFormId()
{
    $post = [
        'correction_form_id' => '999',
    ];
    
    $result = extractConditionalUsePermitFormData($post);
    
    $this->assertIsInt($result['correction_form_id']);
    $this->assertEquals(999, $result['correction_form_id']);
}

public function testExtractConditionalUsePermitFormDataHandlesFormPaidBool()
{
    $post1 = [
        'form_paid_bool' => '1',
    ];
    
    $result1 = extractConditionalUsePermitFormData($post1);
    $this->assertEquals(1, $result1['form_paid_bool']);
    
    $post2 = [];
    $result2 = extractConditionalUsePermitFormData($post2);
    $this->assertEquals(0, $result2['form_paid_bool']);
}

public function testExtractConditionalUsePermitFormDataHandlesFileUploadErrors()
{
    $files = [
        'file_exhibit' => ['error' => UPLOAD_ERR_NO_FILE],
        'file_adjacent' => ['error' => UPLOAD_ERR_INI_SIZE],
    ];
    
    $result = extractConditionalUsePermitFormData([], $files);
    
    $this->assertNull($result['file_exhibit']);
    $this->assertNull($result['file_adjacent']);
}

public function testExtractConditionalUsePermitFormDataStoresOnlyFilename()
{
    $files = [
        'file_exhibit' => ['error' => UPLOAD_ERR_OK, 'name' => 'my_exhibit.pdf'],
    ];
    
    $result = extractConditionalUsePermitFormData([], $files);
    
    $this->assertEquals('my_exhibit.pdf', $result['file_exhibit']);
}

// ==================== validateConditionalUsePermitData Tests ====================

public function testValidateConditionalUsePermitDataWithValidData()
{
    $formData = [
        'applicant_name' => 'Test CUP LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_email' => 'test@example.com',
        'application_fee' => '150.00',
    ];
    
    $errors = validateConditionalUsePermitData($formData);
    
    $this->assertEmpty($errors);
}

public function testValidateConditionalUsePermitDataWithMissingRequired()
{
    $formData = [];
    
    $errors = validateConditionalUsePermitData($formData);
    
    $this->assertNotEmpty($errors);
    $this->assertContains('Applicant name is required', $errors);
    $this->assertContains('At least one signature is required', $errors);
    $this->assertContains('At least one signature date is required', $errors);
}

public function testValidateConditionalUsePermitDataWithInvalidPhone()
{
    $formData = [
        'applicant_name' => 'Test CUP LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_phone' => 'not-a-phone',
    ];
    
    $errors = validateConditionalUsePermitData($formData);
    
    $this->assertContains('Applicant phone format is invalid', $errors);
}

public function testValidateConditionalUsePermitDataWithMultipleInvalidPhones()
{
    $formData = [
        'applicant_name' => 'Test CUP LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_phone' => 'invalid',
        'owner_phone' => 'also-invalid',
        'attorney_phone' => 'wrong',
    ];
    
    $errors = validateConditionalUsePermitData($formData);
    
    $this->assertContains('Applicant phone format is invalid', $errors);
    $this->assertContains('Owner phone format is invalid', $errors);
    $this->assertContains('Attorney phone format is invalid', $errors);
}

public function testValidateConditionalUsePermitDataWithValidPhoneFormats()
{
    $validPhones = [
        '555-1234',
        '(555) 123-4567',
        '555.123.4567',
        '5551234567',
        '555 123 4567',
    ];
    
    foreach ($validPhones as $phone) {
        $formData = [
            'applicant_name' => 'Test CUP LLC',
            'signature_name_1' => 'John Doe',
            'signature_date_1' => '2024-02-01',
            'applicant_phone' => $phone,
        ];
        
        $errors = validateConditionalUsePermitData($formData);
        $this->assertEmpty($errors, "Phone format '$phone' should be valid");
    }
}

public function testValidateConditionalUsePermitDataWithInvalidEmail()
{
    $formData = [
        'applicant_name' => 'Test CUP LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_email' => 'invalid-email',
    ];
    
    $errors = validateConditionalUsePermitData($formData);
    
    $this->assertContains('Applicant email format is invalid', $errors);
}

public function testValidateConditionalUsePermitDataWithMultipleInvalidEmails()
{
    $formData = [
        'applicant_name' => 'Test CUP LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_email' => 'invalid',
        'owner_email' => 'also-invalid',
        'attorney_email' => 'wrong@',
    ];
    
    $errors = validateConditionalUsePermitData($formData);
    
    $this->assertContains('Applicant email format is invalid', $errors);
    $this->assertContains('Owner email format is invalid', $errors);
    $this->assertContains('Attorney email format is invalid', $errors);
}

public function testValidateConditionalUsePermitDataWithInvalidApplicationFee()
{
    $formData = [
        'applicant_name' => 'Test CUP LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'application_fee' => 'not-a-number',
    ];
    
    $errors = validateConditionalUsePermitData($formData);
    
    $this->assertContains('Application fee must be a valid number', $errors);
}

public function testValidateConditionalUsePermitDataWithInvalidCertificateFee()
{
    $formData = [
        'applicant_name' => 'Test CUP LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'certificate_fee' => 'abc',
    ];
    
    $errors = validateConditionalUsePermitData($formData);
    
    $this->assertContains('Certificate fee must be a valid number', $errors);
}

public function testValidateConditionalUsePermitDataWithValidFees()
{
    $validFees = ['150', '150.00', '0', '999.99'];
    
    foreach ($validFees as $fee) {
        $formData = [
            'applicant_name' => 'Test CUP LLC',
            'signature_name_1' => 'John Doe',
            'signature_date_1' => '2024-02-01',
            'application_fee' => $fee,
            'certificate_fee' => $fee,
        ];
        
        $errors = validateConditionalUsePermitData($formData);
        $this->assertEmpty($errors, "Fee '$fee' should be valid");
    }
}

public function testValidateConditionalUsePermitDataAcceptsSecondSignature()
{
    $formData = [
        'applicant_name' => 'Test CUP LLC',
        'signature_name_2' => 'Jane Smith',
        'signature_date_2' => '2024-02-01',
    ];
    
    $errors = validateConditionalUsePermitData($formData);
    
    $this->assertEmpty($errors);
}

// ==================== insertConditionalUsePermitApplication Tests ====================

public function testInsertConditionalUsePermitApplicationWithPrepareFailure()
{
    $fakeConn = new FakeMysqliConnection(false, 'Prepare error message');
    
    $formData = extractConditionalUsePermitFormData([]);
    $result = insertConditionalUsePermitApplication($fakeConn, $formData);
    
    $this->assertFalse($result['success']);
    $this->assertStringContainsString('Prepare failed', $result['message']);
    $this->assertNull($result['form_id']);
}

public function testInsertConditionalUsePermitApplicationReturnsCorrectStructure()
{
    $fakeConn = new FakeMysqliConnection(false, 'Test error');
    
    $formData = extractConditionalUsePermitFormData([]);
    $result = insertConditionalUsePermitApplication($fakeConn, $formData);
    
    $this->assertIsArray($result);
    $this->assertArrayHasKey('success', $result);
    $this->assertArrayHasKey('message', $result);
    $this->assertArrayHasKey('form_id', $result);
}

// ==================== Full Workflow Tests ====================

public function testConditionalUsePermitFullWorkflowWithValidData()
{
    $post = [
        'applicant_name' => 'Test CUP LLC',
        'officers_names' => ['John Doe', 'Jane Smith'],
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'applicant_email' => 'test@example.com',
        'cup_request' => 'Request for conditional use',
        'checklist_application' => '1',
        'application_fee' => '150.00',
    ];
    
    $formData = extractConditionalUsePermitFormData($post);
    $errors = validateConditionalUsePermitData($formData);
    
    $this->assertEmpty($errors);
    $this->assertEquals('Test CUP LLC', $formData['applicant_name']);
    $this->assertEquals(1, $formData['checklist_application']);
}

public function testConditionalUsePermitFullWorkflowWithInvalidData()
{
    $post = [
        'applicant_name' => '',
        'applicant_email' => 'invalid-email',
        'application_fee' => 'not-a-number',
    ];
    
    $formData = extractConditionalUsePermitFormData($post);
    $errors = validateConditionalUsePermitData($formData);
    
    $this->assertNotEmpty($errors);
    $this->assertGreaterThan(3, count($errors));
}

public function testConditionalUsePermitFullWorkflowWithFilesAndValidation()
{
    $post = [
        'applicant_name' => 'Test CUP LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'checklist_exhibit' => '1',
        'checklist_adjacent' => '1',
    ];
    
    $files = [
        'file_exhibit' => ['error' => UPLOAD_ERR_OK, 'name' => 'exhibit.pdf'],
        'file_adjacent' => ['error' => UPLOAD_ERR_OK, 'name' => 'adjacent.pdf'],
    ];
    
    $formData = extractConditionalUsePermitFormData($post, $files);
    $errors = validateConditionalUsePermitData($formData);
    
    $this->assertEmpty($errors);
    $this->assertEquals('exhibit.pdf', $formData['file_exhibit']);
    $this->assertEquals('adjacent.pdf', $formData['file_adjacent']);
    $this->assertEquals(1, $formData['checklist_exhibit']);
    $this->assertEquals(1, $formData['checklist_adjacent']);
}

public function testConditionalUsePermitFormDataConvertsArraysToJson()
{
    $post = [
        'officers_names' => ['Officer 1', 'Officer 2', ''],
        'additional_applicant_names' => ['Applicant A', 'Applicant B'],
        'additional_owner_names' => ['Owner A', '', 'Owner B'],
    ];
    
    $result = extractConditionalUsePermitFormData($post);
    
    // Check officers_names (filtered)
    $decoded = json_decode($result['officers_names'], true);
    $this->assertCount(2, $decoded);
    $this->assertContains('Officer 1', $decoded);
    $this->assertContains('Officer 2', $decoded);
    
    // Check additional_applicant_names (filtered)
    $decoded = json_decode($result['additional_applicant_names'], true);
    $this->assertCount(2, $decoded);
    $this->assertContains('Applicant A', $decoded);
    
    // Check additional_owner_names (filtered)
    $decoded = json_decode($result['additional_owner_names'], true);
    $this->assertCount(2, $decoded);
    $this->assertContains('Owner A', $decoded);
    $this->assertContains('Owner B', $decoded);
}

public function testConditionalUsePermitFormDataHandlesAdditionalArrays()
{
    $post = [
        'applicant_name' => 'Test CUP LLC',
        'signature_name_1' => 'John Doe',
        'signature_date_1' => '2024-02-01',
        'additional_applicant_mailing_addresses' => ['123 Main St', '456 Oak Ave'],
        'additional_owner_phones' => ['555-1111', '555-2222'],
        'additional_applicant_emails' => ['email1@test.com', 'email2@test.com'],
    ];
    
    $result = extractConditionalUsePermitFormData($post);
    $errors = validateConditionalUsePermitData($result);
    
    $this->assertEmpty($errors);
    
    // Verify arrays were JSON encoded
    $this->assertIsString($result['additional_applicant_mailing_addresses']);
    $this->assertIsString($result['additional_owner_phones']);
    $this->assertIsString($result['additional_applicant_emails']);
    
    // Verify content
    $addresses = json_decode($result['additional_applicant_mailing_addresses'], true);
    $this->assertContains('123 Main St', $addresses);
}

// ==================== extractAdministrativeAppealFormData Tests ====================

public function testExtractAdministrativeAppealFormDataWithFullData()
{
    $post = [
        'p_correction_form_id' => '999',
        'p_aar_hearing_date' => '2024-03-15',
        'p_aar_submit_date' => '2024-02-01',
        'p_aar_street_address' => '123 Main St',
        'p_aar_city_address' => 'Springfield',
        'p_state_code' => 'KY',
        'p_aar_zip_code' => '40423',
        'p_aar_property_location' => 'Lot 5, Block 3',
        'p_aar_official_decision' => 'Decision to deny permit',
        'p_aar_relevant_provisions' => 'Section 5.2.3 of Zoning Ordinance',
        'p_aar_appellant_first_name' => 'John',
        'p_aar_appellant_last_name' => 'Doe',
        'appellants_names' => ['Jane Smith', 'Bob Johnson'],
        'p_adjacent_property_owner_street' => '125 Main St',
        'p_adjacent_property_owner_city' => 'Springfield',
        'p_adjacent_property_owner_state_code' => 'KY',
        'p_adjacent_property_owner_zip' => '40423',
        'p_aar_property_owner_first_name' => 'Mary',
        'p_aar_property_owner_last_name' => 'Owner',
        'property_owners_names' => ['Tom Owner', 'Sue Owner'],
    ];
    
    $result = extractAdministrativeAppealFormData($post);
    
    $this->assertEquals(999, $result['correction_form_id']);
    $this->assertEquals('2024-03-15', $result['hearing_date']);
    $this->assertEquals('2024-02-01', $result['submit_date']);
    $this->assertEquals('123 Main St', $result['street_address']);
    $this->assertEquals('Springfield', $result['city_address']);
    $this->assertEquals('KY', $result['state_code']);
    $this->assertEquals('40423', $result['zip_code']);
    $this->assertEquals('Lot 5, Block 3', $result['property_location']);
    $this->assertEquals('John', $result['appellant_first_name']);
    $this->assertEquals('Doe', $result['appellant_last_name']);
    $this->assertEquals('Mary', $result['property_owner_first_name']);
    $this->assertEquals('Owner', $result['property_owner_last_name']);
    $this->assertEquals(0, $result['form_paid_bool']);
}

public function testExtractAdministrativeAppealFormDataWithMinimalData()
{
    $post = [];
    
    $result = extractAdministrativeAppealFormData($post);
    
    $this->assertIsArray($result);
    $this->assertEquals('', $result['appellant_first_name']);
    $this->assertEquals('', $result['street_address']);
    $this->assertNull($result['correction_form_id']);
    $this->assertEquals(0, $result['form_paid_bool']);
}

public function testExtractAdministrativeAppealFormDataDefaultsSubmitDateToToday()
{
    $post = [];
    
    $result = extractAdministrativeAppealFormData($post);
    
    $this->assertEquals(date('Y-m-d'), $result['submit_date']);
}

public function testExtractAdministrativeAppealFormDataUsesProvidedSubmitDate()
{
    $post = [
        'p_aar_submit_date' => '2024-01-15',
    ];
    
    $result = extractAdministrativeAppealFormData($post);
    
    $this->assertEquals('2024-01-15', $result['submit_date']);
}

public function testExtractAdministrativeAppealFormDataTrimsWhitespace()
{
    $post = [
        'p_aar_street_address' => '  123 Main St  ',
        'p_aar_city_address' => '  Springfield  ',
        'p_state_code' => '  KY  ',
        'p_aar_zip_code' => '  40423  ',
        'p_aar_appellant_first_name' => '  John  ',
        'p_aar_appellant_last_name' => '  Doe  ',
    ];
    
    $result = extractAdministrativeAppealFormData($post);
    
    $this->assertEquals('123 Main St', $result['street_address']);
    $this->assertEquals('Springfield', $result['city_address']);
    $this->assertEquals('KY', $result['state_code']);
    $this->assertEquals('40423', $result['zip_code']);
    $this->assertEquals('John', $result['appellant_first_name']);
    $this->assertEquals('Doe', $result['appellant_last_name']);
}

public function testExtractAdministrativeAppealFormDataHandlesAdditionalAppellants()
{
    $post = [
        'appellants_names' => ['Jane Smith', '', 'Bob Johnson', ''],
    ];
    
    $result = extractAdministrativeAppealFormData($post);
    
    $this->assertNotNull($result['additional_appellants']);
    $decoded = json_decode($result['additional_appellants'], true);
    $this->assertCount(2, $decoded);
    $this->assertContains('Jane Smith', $decoded);
    $this->assertContains('Bob Johnson', $decoded);
    $this->assertNotContains('', $decoded);
}

public function testExtractAdministrativeAppealFormDataHandlesAdditionalPropertyOwners()
{
    $post = [
        'property_owners_names' => ['Tom Owner', 'Sue Owner', ''],
    ];
    
    $result = extractAdministrativeAppealFormData($post);
    
    $this->assertNotNull($result['additional_property_owners']);
    $decoded = json_decode($result['additional_property_owners'], true);
    $this->assertCount(2, $decoded);
    $this->assertContains('Tom Owner', $decoded);
    $this->assertContains('Sue Owner', $decoded);
}

public function testExtractAdministrativeAppealFormDataHandlesEmptyArrays()
{
    $post = [
        'appellants_names' => [],
        'property_owners_names' => [],
    ];
    
    $result = extractAdministrativeAppealFormData($post);
    
    $this->assertNull($result['additional_appellants']);
    $this->assertNull($result['additional_property_owners']);
}

public function testExtractAdministrativeAppealFormDataHandlesArraysWithOnlyEmptyStrings()
{
    $post = [
        'appellants_names' => ['', '', ''],
        'property_owners_names' => [''],
    ];
    
    $result = extractAdministrativeAppealFormData($post);
    
    $this->assertNull($result['additional_appellants']);
    $this->assertNull($result['additional_property_owners']);
}

public function testExtractAdministrativeAppealFormDataAlwaysSetsFormPaidBoolToZero()
{
    $post1 = ['form_paid_bool' => '1'];
    $result1 = extractAdministrativeAppealFormData($post1);
    $this->assertEquals(0, $result1['form_paid_bool']);
    
    $post2 = [];
    $result2 = extractAdministrativeAppealFormData($post2);
    $this->assertEquals(0, $result2['form_paid_bool']);
}

// ==================== validateAdministrativeAppealData Tests ====================

public function testValidateAdministrativeAppealDataWithValidData()
{
    $formData = [
        'appellant_first_name' => 'John',
        'appellant_last_name' => 'Doe',
        'property_owner_first_name' => 'Mary',
        'property_owner_last_name' => 'Owner',
        'street_address' => '123 Main St',
        'city_address' => 'Springfield',
        'state_code' => 'KY',
        'zip_code' => '40423',
        'property_location' => 'Lot 5, Block 3',
        'official_decision' => 'Decision to deny',
        'relevant_provisions' => 'Section 5.2.3',
    ];
    
    $errors = validateAdministrativeAppealData($formData);
    
    $this->assertEmpty($errors);
}

public function testValidateAdministrativeAppealDataWithMissingAppellantFirstName()
{
    $formData = [
        'appellant_first_name' => '',
        'appellant_last_name' => 'Doe',
        'property_owner_first_name' => 'Mary',
        'property_owner_last_name' => 'Owner',
        'street_address' => '123 Main St',
        'city_address' => 'Springfield',
        'state_code' => 'KY',
        'zip_code' => '40423',
        'property_location' => 'Lot 5, Block 3',
        'official_decision' => 'Decision to deny',
        'relevant_provisions' => 'Section 5.2.3',
    ];
    
    $errors = validateAdministrativeAppealData($formData);
    
    $this->assertContains("Appellant's first name is required", $errors);
}

public function testValidateAdministrativeAppealDataWithMissingAppellantLastName()
{
    $formData = [
        'appellant_first_name' => 'John',
        'appellant_last_name' => '',
        'property_owner_first_name' => 'Mary',
        'property_owner_last_name' => 'Owner',
        'street_address' => '123 Main St',
        'city_address' => 'Springfield',
        'state_code' => 'KY',
        'zip_code' => '40423',
        'property_location' => 'Lot 5, Block 3',
        'official_decision' => 'Decision to deny',
        'relevant_provisions' => 'Section 5.2.3',
    ];
    
    $errors = validateAdministrativeAppealData($formData);
    
    $this->assertContains("Appellant's last name is required", $errors);
}

public function testValidateAdministrativeAppealDataWithMissingPropertyOwnerInfo()
{
    $formData = [
        'appellant_first_name' => 'John',
        'appellant_last_name' => 'Doe',
        'property_owner_first_name' => '',
        'property_owner_last_name' => '',
        'street_address' => '123 Main St',
        'city_address' => 'Springfield',
        'state_code' => 'KY',
        'zip_code' => '40423',
        'property_location' => 'Lot 5, Block 3',
        'official_decision' => 'Decision to deny',
        'relevant_provisions' => 'Section 5.2.3',
    ];
    
    $errors = validateAdministrativeAppealData($formData);
    
    $this->assertContains("Property owner's first name is required", $errors);
    $this->assertContains("Property owner's last name is required", $errors);
}

public function testValidateAdministrativeAppealDataWithMissingAddressFields()
{
    $formData = [
        'appellant_first_name' => 'John',
        'appellant_last_name' => 'Doe',
        'property_owner_first_name' => 'Mary',
        'property_owner_last_name' => 'Owner',
        'street_address' => '',
        'city_address' => '',
        'state_code' => '',
        'zip_code' => '',
        'property_location' => 'Lot 5, Block 3',
        'official_decision' => 'Decision to deny',
        'relevant_provisions' => 'Section 5.2.3',
    ];
    
    $errors = validateAdministrativeAppealData($formData);
    
    $this->assertContains('Street address is required', $errors);
    $this->assertContains('City is required', $errors);
    $this->assertContains('State is required', $errors);
    $this->assertContains('ZIP code is required', $errors);
}

public function testValidateAdministrativeAppealDataWithInvalidZipCode()
{
    $formData = [
        'appellant_first_name' => 'John',
        'appellant_last_name' => 'Doe',
        'property_owner_first_name' => 'Mary',
        'property_owner_last_name' => 'Owner',
        'street_address' => '123 Main St',
        'city_address' => 'Springfield',
        'state_code' => 'KY',
        'zip_code' => 'INVALID',
        'property_location' => 'Lot 5, Block 3',
        'official_decision' => 'Decision to deny',
        'relevant_provisions' => 'Section 5.2.3',
    ];
    
    $errors = validateAdministrativeAppealData($formData);
    
    $this->assertContains('ZIP code must be in format 12345 or 12345-6789', $errors);
}

public function testValidateAdministrativeAppealDataWithValidZipCodeFormats()
{
    $formData1 = [
        'appellant_first_name' => 'John',
        'appellant_last_name' => 'Doe',
        'property_owner_first_name' => 'Mary',
        'property_owner_last_name' => 'Owner',
        'street_address' => '123 Main St',
        'city_address' => 'Springfield',
        'state_code' => 'KY',
        'zip_code' => '40423',
        'property_location' => 'Lot 5, Block 3',
        'official_decision' => 'Decision to deny',
        'relevant_provisions' => 'Section 5.2.3',
    ];
    
    $errors1 = validateAdministrativeAppealData($formData1);
    $this->assertEmpty($errors1);
    
    $formData2 = [
        'appellant_first_name' => 'John',
        'appellant_last_name' => 'Doe',
        'property_owner_first_name' => 'Mary',
        'property_owner_last_name' => 'Owner',
        'street_address' => '123 Main St',
        'city_address' => 'Springfield',
        'state_code' => 'KY',
        'zip_code' => '40423-1234',
        'property_location' => 'Lot 5, Block 3',
        'official_decision' => 'Decision to deny',
        'relevant_provisions' => 'Section 5.2.3',
    ];
    
    $errors2 = validateAdministrativeAppealData($formData2);
    $this->assertEmpty($errors2);
}

public function testValidateAdministrativeAppealDataWithInvalidAdjacentZipCode()
{
    $formData = [
        'appellant_first_name' => 'John',
        'appellant_last_name' => 'Doe',
        'property_owner_first_name' => 'Mary',
        'property_owner_last_name' => 'Owner',
        'street_address' => '123 Main St',
        'city_address' => 'Springfield',
        'state_code' => 'KY',
        'zip_code' => '40423',
        'property_location' => 'Lot 5, Block 3',
        'official_decision' => 'Decision to deny',
        'relevant_provisions' => 'Section 5.2.3',
        'adjacent_property_owner_zip' => 'INVALID',
    ];
    
    $errors = validateAdministrativeAppealData($formData);
    
    $this->assertContains('Adjacent property owner ZIP code must be in format 12345 or 12345-6789', $errors);
}

public function testValidateAdministrativeAppealDataWithMissingAppealDetails()
{
    $formData = [
        'appellant_first_name' => 'John',
        'appellant_last_name' => 'Doe',
        'property_owner_first_name' => 'Mary',
        'property_owner_last_name' => 'Owner',
        'street_address' => '123 Main St',
        'city_address' => 'Springfield',
        'state_code' => 'KY',
        'zip_code' => '40423',
        'property_location' => '',
        'official_decision' => '',
        'relevant_provisions' => '',
    ];
    
    $errors = validateAdministrativeAppealData($formData);
    
    $this->assertContains('Location of property is required', $errors);
    $this->assertContains('Decision of official is required', $errors);
    $this->assertContains('Relevant provisions of zoning ordinance are required', $errors);
}

public function testValidateAdministrativeAppealDataWithAllFieldsMissing()
{
    $formData = [
        'appellant_first_name' => '',
        'appellant_last_name' => '',
        'property_owner_first_name' => '',
        'property_owner_last_name' => '',
        'street_address' => '',
        'city_address' => '',
        'state_code' => '',
        'zip_code' => '',
        'property_location' => '',
        'official_decision' => '',
        'relevant_provisions' => '',
    ];
    
    $errors = validateAdministrativeAppealData($formData);
    
    $this->assertGreaterThan(10, count($errors));
}

public function testValidateAdministrativeAppealDataAdjacentFieldsAreOptional()
{
    $formData = [
        'appellant_first_name' => 'John',
        'appellant_last_name' => 'Doe',
        'property_owner_first_name' => 'Mary',
        'property_owner_last_name' => 'Owner',
        'street_address' => '123 Main St',
        'city_address' => 'Springfield',
        'state_code' => 'KY',
        'zip_code' => '40423',
        'property_location' => 'Lot 5, Block 3',
        'official_decision' => 'Decision to deny',
        'relevant_provisions' => 'Section 5.2.3',
        'adjacent_property_owner_street' => '',
        'adjacent_property_owner_city' => '',
        'adjacent_property_owner_state_code' => '',
        'adjacent_property_owner_zip' => '',
    ];
    
    $errors = validateAdministrativeAppealData($formData);
    
    $this->assertEmpty($errors);
}

// ==================== insertAdministrativeAppealApplication Tests ====================

public function testInsertAdministrativeAppealApplicationWithPrepareFailure()
{
    $fakeConn = new FakeMysqliConnection(false, 'Prepare error message');
    
    $formData = extractAdministrativeAppealFormData([]);
    $result = insertAdministrativeAppealApplication($fakeConn, $formData);
    
    $this->assertFalse($result['success']);
    $this->assertStringContainsString('Prepare failed', $result['message']);
    $this->assertNull($result['form_id']);
}

public function testInsertAdministrativeAppealApplicationReturnsCorrectStructure()
{
    $fakeConn = new FakeMysqliConnection(false, 'Test error');
    
    $formData = extractAdministrativeAppealFormData([]);
    $result = insertAdministrativeAppealApplication($fakeConn, $formData);
    
    $this->assertIsArray($result);
    $this->assertArrayHasKey('success', $result);
    $this->assertArrayHasKey('message', $result);
    $this->assertArrayHasKey('form_id', $result);
}

// ==================== Full Workflow Tests ====================

public function testAdministrativeAppealFullWorkflowWithValidData()
{
    $post = [
        'p_aar_appellant_first_name' => 'John',
        'p_aar_appellant_last_name' => 'Doe',
        'p_aar_property_owner_first_name' => 'Mary',
        'p_aar_property_owner_last_name' => 'Owner',
        'p_aar_street_address' => '123 Main St',
        'p_aar_city_address' => 'Springfield',
        'p_state_code' => 'KY',
        'p_aar_zip_code' => '40423',
        'p_aar_property_location' => 'Lot 5, Block 3',
        'p_aar_official_decision' => 'Decision to deny',
        'p_aar_relevant_provisions' => 'Section 5.2.3',
    ];
    
    $formData = extractAdministrativeAppealFormData($post);
    $errors = validateAdministrativeAppealData($formData);
    
    $this->assertEmpty($errors);
    $this->assertEquals('John', $formData['appellant_first_name']);
    $this->assertEquals('Mary', $formData['property_owner_first_name']);
}

public function testAdministrativeAppealFullWorkflowWithInvalidData()
{
    $post = [
        'p_aar_appellant_first_name' => '',
        'p_aar_zip_code' => 'INVALID',
    ];
    
    $formData = extractAdministrativeAppealFormData($post);
    $errors = validateAdministrativeAppealData($formData);
    
    $this->assertNotEmpty($errors);
    $this->assertGreaterThan(5, count($errors));
}

public function testAdministrativeAppealFullWorkflowWithAdditionalPeople()
{
    $post = [
        'p_aar_appellant_first_name' => 'John',
        'p_aar_appellant_last_name' => 'Doe',
        'appellants_names' => ['Jane Smith', 'Bob Johnson'],
        'p_aar_property_owner_first_name' => 'Mary',
        'p_aar_property_owner_last_name' => 'Owner',
        'property_owners_names' => ['Tom Owner', 'Sue Owner'],
        'p_aar_street_address' => '123 Main St',
        'p_aar_city_address' => 'Springfield',
        'p_state_code' => 'KY',
        'p_aar_zip_code' => '40423',
        'p_aar_property_location' => 'Lot 5, Block 3',
        'p_aar_official_decision' => 'Decision to deny',
        'p_aar_relevant_provisions' => 'Section 5.2.3',
    ];
    
    $formData = extractAdministrativeAppealFormData($post);
    $errors = validateAdministrativeAppealData($formData);
    
    $this->assertEmpty($errors);
    
    $additionalAppellants = json_decode($formData['additional_appellants'], true);
    $this->assertCount(2, $additionalAppellants);
    
    $additionalOwners = json_decode($formData['additional_property_owners'], true);
    $this->assertCount(2, $additionalOwners);
}

// ==================== extractAdjacentPropertyOwnersFormData Tests ====================

public function testExtractAdjacentPropertyOwnersFormDataWithSinglePropertySingleOwner()
{
    $post = [
        'num_neighbors' => 1,
        'p_PVA_map_code' => ['123-456-789'],
        'p_apof_neighbor_property_location' => ['123 Main St'],
        'p_apof_neighbor_property_deed_book' => ['Book 1'],
        'p_apof_property_street_pg_number' => ['Page 10'],
        'num_owners' => [1],
        'p_adjacent_property_owner_name' => [
            ['John Doe']
        ],
        'p_adjacent_property_owner_street' => [
            ['456 Oak Ave']
        ],
        'p_adjacent_property_owner_city' => [
            ['Springfield']
        ],
        'p_adjacent_state_code' => [
            ['KY']
        ],
        'p_adjacent_property_owner_zip' => [
            ['40423']
        ],
    ];
    
    $result = extractAdjacentPropertyOwnersFormData($post);
    
    // Verify structure
    $this->assertIsArray($result);
    $this->assertEquals(0, $result['form_paid_bool']);
    
    // Decode and verify property data
    $pva_codes = json_decode($result['pva_map_codes'], true);
    $this->assertCount(1, $pva_codes);
    $this->assertEquals('123-456-789', $pva_codes[0]);
    
    $locations = json_decode($result['neighbor_property_locations'], true);
    $this->assertEquals('123 Main St', $locations[0]);
    
    // Decode and verify owner data (nested structure)
    $owner_names = json_decode($result['property_owner_names'], true);
    $this->assertCount(1, $owner_names['0']);
    $this->assertEquals('John Doe', $owner_names['0'][0]);
    
    $owner_zips = json_decode($result['property_owner_zips'], true);
    $this->assertEquals('40423', $owner_zips['0'][0]);
}

public function testExtractAdjacentPropertyOwnersFormDataWithMultiplePropertiesMultipleOwners()
{
    $post = [
        'num_neighbors' => 2,
        'p_PVA_map_code' => ['123-456-789', '987-654-321'],
        'p_apof_neighbor_property_location' => ['123 Main St', '456 Oak Ave'],
        'p_apof_neighbor_property_deed_book' => ['Book 1', 'Book 2'],
        'p_apof_property_street_pg_number' => ['Page 10', 'Page 20'],
        'num_owners' => [2, 1],
        'p_adjacent_property_owner_name' => [
            ['John Doe', 'Jane Smith'],
            ['Bob Johnson']
        ],
        'p_adjacent_property_owner_street' => [
            ['456 Oak Ave', '789 Elm St'],
            ['321 Pine St']
        ],
        'p_adjacent_property_owner_city' => [
            ['Springfield', 'Springfield'],
            ['Danville']
        ],
        'p_adjacent_state_code' => [
            ['KY', 'KY'],
            ['KY']
        ],
        'p_adjacent_property_owner_zip' => [
            ['40423', '40423-1234'],
            ['40422']
        ],
    ];
    
    $result = extractAdjacentPropertyOwnersFormData($post);
    
    // Verify property data
    $pva_codes = json_decode($result['pva_map_codes'], true);
    $this->assertCount(2, $pva_codes);
    $this->assertEquals('987-654-321', $pva_codes[1]);
    
    // Verify first property has 2 owners
    $owner_names = json_decode($result['property_owner_names'], true);
    $this->assertCount(2, $owner_names['0']);
    $this->assertEquals('John Doe', $owner_names['0'][0]);
    $this->assertEquals('Jane Smith', $owner_names['0'][1]);
    
    // Verify second property has 1 owner
    $this->assertCount(1, $owner_names['1']);
    $this->assertEquals('Bob Johnson', $owner_names['1'][0]);
}

public function testExtractAdjacentPropertyOwnersFormDataWithEmptyData()
{
    $post = [
        'num_neighbors' => 0,
    ];
    
    $result = extractAdjacentPropertyOwnersFormData($post);
    
    $pva_codes = json_decode($result['pva_map_codes'], true);
    $this->assertEmpty($pva_codes);
    
    $owner_names = json_decode($result['property_owner_names'], true);
    $this->assertEmpty($owner_names);
}

public function testExtractAdjacentPropertyOwnersFormDataHandlesMissingOptionalFields()
{
    $post = [
        'num_neighbors' => 1,
        'p_PVA_map_code' => ['123-456-789'],
        'p_apof_neighbor_property_location' => ['123 Main St'],
        // Deed book and page number omitted
        'num_owners' => [1],
        'p_adjacent_property_owner_name' => [
            ['John Doe']
        ],
        // Address fields omitted
    ];
    
    $result = extractAdjacentPropertyOwnersFormData($post);
    
    $deed_books = json_decode($result['neighbor_property_deed_books'], true);
    $this->assertNull($deed_books[0]);
    
    $page_numbers = json_decode($result['property_street_pg_numbers'], true);
    $this->assertNull($page_numbers[0]);
    
    $owner_streets = json_decode($result['property_owner_streets'], true);
    $this->assertEquals('', $owner_streets['0'][0]);
}

public function testExtractAdjacentPropertyOwnersFormDataHandlesCorrectionFormId()
{
    $post = [
        'num_neighbors' => 0,
        'p_correction_form_id' => '999',
    ];
    
    $result = extractAdjacentPropertyOwnersFormData($post);
    
    $this->assertEquals(999, $result['correction_form_id']);
    $this->assertIsInt($result['correction_form_id']);
}

public function testExtractAdjacentPropertyOwnersFormDataAlwaysSetsFormPaidBoolToZero()
{
    $post1 = ['num_neighbors' => 0];
    $result1 = extractAdjacentPropertyOwnersFormData($post1);
    $this->assertEquals(0, $result1['form_paid_bool']);
    
    $post2 = ['num_neighbors' => 0, 'form_paid_bool' => '1'];
    $result2 = extractAdjacentPropertyOwnersFormData($post2);
    $this->assertEquals(0, $result2['form_paid_bool']);
}

public function testExtractAdjacentPropertyOwnersFormDataCreatesNestedStructure()
{
    $post = [
        'num_neighbors' => 2,
        'p_PVA_map_code' => ['ABC', 'DEF'],
        'p_apof_neighbor_property_location' => ['Loc 1', 'Loc 2'],
        'num_owners' => [1, 2],
        'p_adjacent_property_owner_name' => [
            ['Owner A'],
            ['Owner B', 'Owner C']
        ],
        'p_adjacent_property_owner_street' => [
            ['Street A'],
            ['Street B', 'Street C']
        ],
    ];
    
    $result = extractAdjacentPropertyOwnersFormData($post);
    
    // Verify the nested JSON structure
    $owner_names = json_decode($result['property_owner_names'], true);
    
    // Check structure is object-like (keyed by neighbor index)
    $this->assertArrayHasKey('0', $owner_names);
    $this->assertArrayHasKey('1', $owner_names);
    
    // Check first neighbor has 1 owner
    $this->assertIsArray($owner_names['0']);
    $this->assertCount(1, $owner_names['0']);
    
    // Check second neighbor has 2 owners
    $this->assertIsArray($owner_names['1']);
    $this->assertCount(2, $owner_names['1']);
}

// ==================== validateAdjacentPropertyOwnersFormData Tests ====================

public function testValidateAdjacentPropertyOwnersFormDataWithValidData()
{
    $post = [
        'num_neighbors' => 1,
        'p_PVA_map_code' => ['123-456-789'],
        'p_apof_neighbor_property_location' => ['123 Main St'],
        'num_owners' => [1],
        'p_adjacent_property_owner_name' => [
            ['John Doe']
        ],
        'p_adjacent_property_owner_zip' => [
            ['40423']
        ],
    ];
    
    $formData = extractAdjacentPropertyOwnersFormData($post);
    $errors = validateAdjacentPropertyOwnersFormData($formData);
    
    $this->assertEmpty($errors);
}

public function testValidateAdjacentPropertyOwnersFormDataWithNoProperties()
{
    $post = [
        'num_neighbors' => 0,
    ];
    
    $formData = extractAdjacentPropertyOwnersFormData($post);
    $errors = validateAdjacentPropertyOwnersFormData($formData);
    
    $this->assertContains('At least one adjacent property is required', $errors);
}

public function testValidateAdjacentPropertyOwnersFormDataWithMissingPVACode()
{
    $post = [
        'num_neighbors' => 1,
        'p_PVA_map_code' => [''],
        'p_apof_neighbor_property_location' => ['123 Main St'],
        'num_owners' => [1],
        'p_adjacent_property_owner_name' => [
            ['John Doe']
        ],
    ];
    
    $formData = extractAdjacentPropertyOwnersFormData($post);
    $errors = validateAdjacentPropertyOwnersFormData($formData);
    
    $this->assertContains('PVA MAP Code is required for Adjacent Property #1', $errors);
}

public function testValidateAdjacentPropertyOwnersFormDataWithMissingLocation()
{
    $post = [
        'num_neighbors' => 1,
        'p_PVA_map_code' => ['123-456-789'],
        'p_apof_neighbor_property_location' => [''],
        'num_owners' => [1],
        'p_adjacent_property_owner_name' => [
            ['John Doe']
        ],
    ];
    
    $formData = extractAdjacentPropertyOwnersFormData($post);
    $errors = validateAdjacentPropertyOwnersFormData($formData);
    
    $this->assertContains('Location of Property is required for Adjacent Property #1', $errors);
}

public function testValidateAdjacentPropertyOwnersFormDataWithNoOwners()
{
    $post = [
        'num_neighbors' => 1,
        'p_PVA_map_code' => ['123-456-789'],
        'p_apof_neighbor_property_location' => ['123 Main St'],
        'num_owners' => [0],
        'p_adjacent_property_owner_name' => [
            []
        ],
    ];
    
    $formData = extractAdjacentPropertyOwnersFormData($post);
    $errors = validateAdjacentPropertyOwnersFormData($formData);
    
    $this->assertContains('At least one property owner is required for Adjacent Property #1', $errors);
}

public function testValidateAdjacentPropertyOwnersFormDataWithMissingOwnerName()
{
    $post = [
        'num_neighbors' => 1,
        'p_PVA_map_code' => ['123-456-789'],
        'p_apof_neighbor_property_location' => ['123 Main St'],
        'num_owners' => [1],
        'p_adjacent_property_owner_name' => [
            ['']
        ],
    ];
    
    $formData = extractAdjacentPropertyOwnersFormData($post);
    $errors = validateAdjacentPropertyOwnersFormData($formData);
    
    $this->assertContains('Owner name is required for Adjacent Property #1, Owner #1', $errors);
}

public function testValidateAdjacentPropertyOwnersFormDataWithInvalidZipCode()
{
    $post = [
        'num_neighbors' => 1,
        'p_PVA_map_code' => ['123-456-789'],
        'p_apof_neighbor_property_location' => ['123 Main St'],
        'num_owners' => [1],
        'p_adjacent_property_owner_name' => [
            ['John Doe']
        ],
        'p_adjacent_property_owner_zip' => [
            ['INVALID']
        ],
    ];
    
    $formData = extractAdjacentPropertyOwnersFormData($post);
    $errors = validateAdjacentPropertyOwnersFormData($formData);
    
    // Check for the complete error message including the format hint
    $this->assertContains('Invalid ZIP code format for Adjacent Property #1, Owner #1 (must be 12345 or 12345-6789)', $errors);
}

public function testValidateAdjacentPropertyOwnersFormDataWithValidZipCodeFormats()
{
    $post1 = [
        'num_neighbors' => 1,
        'p_PVA_map_code' => ['123-456-789'],
        'p_apof_neighbor_property_location' => ['123 Main St'],
        'num_owners' => [1],
        'p_adjacent_property_owner_name' => [
            ['John Doe']
        ],
        'p_adjacent_property_owner_zip' => [
            ['40423']
        ],
    ];
    
    $formData1 = extractAdjacentPropertyOwnersFormData($post1);
    $errors1 = validateAdjacentPropertyOwnersFormData($formData1);
    $this->assertEmpty($errors1);
    
    $post2 = [
        'num_neighbors' => 1,
        'p_PVA_map_code' => ['123-456-789'],
        'p_apof_neighbor_property_location' => ['123 Main St'],
        'num_owners' => [1],
        'p_adjacent_property_owner_name' => [
            ['John Doe']
        ],
        'p_adjacent_property_owner_zip' => [
            ['40423-1234']
        ],
    ];
    
    $formData2 = extractAdjacentPropertyOwnersFormData($post2);
    $errors2 = validateAdjacentPropertyOwnersFormData($formData2);
    $this->assertEmpty($errors2);
}

public function testValidateAdjacentPropertyOwnersFormDataWithEmptyZipCodeIsValid()
{
    $post = [
        'num_neighbors' => 1,
        'p_PVA_map_code' => ['123-456-789'],
        'p_apof_neighbor_property_location' => ['123 Main St'],
        'num_owners' => [1],
        'p_adjacent_property_owner_name' => [
            ['John Doe']
        ],
        'p_adjacent_property_owner_zip' => [
            ['']
        ],
    ];
    
    $formData = extractAdjacentPropertyOwnersFormData($post);
    $errors = validateAdjacentPropertyOwnersFormData($formData);
    
    $this->assertEmpty($errors);
}

public function testValidateAdjacentPropertyOwnersFormDataWithMultiplePropertiesAndErrors()
{
    $post = [
        'num_neighbors' => 2,
        'p_PVA_map_code' => ['', '987-654-321'],
        'p_apof_neighbor_property_location' => ['123 Main St', ''],
        'num_owners' => [1, 2],
        'p_adjacent_property_owner_name' => [
            ['John Doe'],
            ['', 'Jane Smith']
        ],
        'p_adjacent_property_owner_zip' => [
            ['INVALID'],
            ['40423', 'INVALID2']
        ],
    ];
    
    $formData = extractAdjacentPropertyOwnersFormData($post);
    $errors = validateAdjacentPropertyOwnersFormData($formData);
    
    $this->assertGreaterThan(3, count($errors));
    $this->assertContains('PVA MAP Code is required for Adjacent Property #1', $errors);
    $this->assertContains('Location of Property is required for Adjacent Property #2', $errors);
    $this->assertContains('Owner name is required for Adjacent Property #2, Owner #1', $errors);
}

// ==================== insertAdjacentPropertyOwnersFormApplication Tests ====================

public function testInsertAdjacentPropertyOwnersFormApplicationWithPrepareFailure()
{
    $fakeConn = new FakeMysqliConnection(false, 'Prepare error message');
    
    $formData = extractAdjacentPropertyOwnersFormData(['num_neighbors' => 0]);
    $result = insertAdjacentPropertyOwnersFormApplication($fakeConn, $formData);
    
    $this->assertFalse($result['success']);
    $this->assertStringContainsString('Prepare failed', $result['message']);
    $this->assertNull($result['form_id']);
}

public function testInsertAdjacentPropertyOwnersFormApplicationReturnsCorrectStructure()
{
    $fakeConn = new FakeMysqliConnection(false, 'Test error');
    
    $formData = extractAdjacentPropertyOwnersFormData(['num_neighbors' => 0]);
    $result = insertAdjacentPropertyOwnersFormApplication($fakeConn, $formData);
    
    $this->assertIsArray($result);
    $this->assertArrayHasKey('success', $result);
    $this->assertArrayHasKey('message', $result);
    $this->assertArrayHasKey('form_id', $result);
}

// ==================== Full Workflow Tests ====================

public function testAdjacentPropertyOwnersFullWorkflowWithValidData()
{
    $post = [
        'num_neighbors' => 1,
        'p_PVA_map_code' => ['123-456-789'],
        'p_apof_neighbor_property_location' => ['123 Main St'],
        'p_apof_neighbor_property_deed_book' => ['Book 1'],
        'p_apof_property_street_pg_number' => ['Page 10'],
        'num_owners' => [1],
        'p_adjacent_property_owner_name' => [
            ['John Doe']
        ],
        'p_adjacent_property_owner_street' => [
            ['456 Oak Ave']
        ],
        'p_adjacent_property_owner_city' => [
            ['Springfield']
        ],
        'p_adjacent_state_code' => [
            ['KY']
        ],
        'p_adjacent_property_owner_zip' => [
            ['40423']
        ],
    ];
    
    $formData = extractAdjacentPropertyOwnersFormData($post);
    $errors = validateAdjacentPropertyOwnersFormData($formData);
    
    $this->assertEmpty($errors);
}

public function testAdjacentPropertyOwnersFullWorkflowWithMultipleProperties()
{
    $post = [
        'num_neighbors' => 3,
        'p_PVA_map_code' => ['123', '456', '789'],
        'p_apof_neighbor_property_location' => ['Loc 1', 'Loc 2', 'Loc 3'],
        'num_owners' => [1, 2, 1],
        'p_adjacent_property_owner_name' => [
            ['Owner A'],
            ['Owner B', 'Owner C'],
            ['Owner D']
        ],
        'p_adjacent_property_owner_zip' => [
            ['40423'],
            ['40423-1234', '40422'],
            ['']
        ],
    ];
    
    $formData = extractAdjacentPropertyOwnersFormData($post);
    $errors = validateAdjacentPropertyOwnersFormData($formData);
    
    $this->assertEmpty($errors);
    
    // Verify structure
    $pva_codes = json_decode($formData['pva_map_codes'], true);
    $this->assertCount(3, $pva_codes);
    
    $owner_names = json_decode($formData['property_owner_names'], true);
    $this->assertCount(1, $owner_names['0']);
    $this->assertCount(2, $owner_names['1']);
    $this->assertCount(1, $owner_names['2']);
}

public function testAdjacentPropertyOwnersFullWorkflowWithInvalidData()
{
    $post = [
        'num_neighbors' => 2,
        'p_PVA_map_code' => ['', '456'],
        'p_apof_neighbor_property_location' => ['Loc 1', ''],
        'num_owners' => [0, 1],
        'p_adjacent_property_owner_name' => [
            [],
            ['']
        ],
    ];
    
    $formData = extractAdjacentPropertyOwnersFormData($post);
    $errors = validateAdjacentPropertyOwnersFormData($formData);
    
    $this->assertNotEmpty($errors);
    $this->assertGreaterThan(3, count($errors));
}

// ==================== extractZoningPermitFormData Tests ====================

public function testExtractZoningPermitFormDataWithFullData()
{
    $post = [
        'application_date' => '2024-02-01',
        'construction_start_date' => '2024-03-01',
        'permit_number' => 'ZP-2024-001',
        'applicant_name' => 'John Doe Construction',
        'applicant_address' => '123 Main St, Springfield, KY 40423',
        'applicant_phone' => '555-1234',
        'applicant_cell' => '555-5678',
        'applicant_email' => 'john@example.com',
        'owner_name' => 'Jane Owner',
        'owner_address' => '456 Oak Ave, Springfield, KY 40423',
        'owner_phone' => '555-9999',
        'owner_cell' => '555-8888',
        'owner_email' => 'jane@example.com',
        'surveyor' => 'ABC Surveying',
        'contractor' => 'XYZ Contractors',
        'architect' => 'Smith Architecture',
        'landscape_architect' => 'Green Landscapes',
        'property_address' => '789 Elm St, Springfield, KY 40423',
        'pva_number' => '123-456-789',
        'acreage' => '5.5',
        'current_zoning' => 'Commercial',
        'project_type' => 'Commercial',
        'structure_type' => 'Office Building',
        'square_feet' => '10000',
        'project_value' => '500000',
    ];
    
    $result = extractZoningPermitFormData($post);
    
    $this->assertEquals('ZP-2024-001', $result['permit_number']);
    $this->assertEquals('John Doe Construction', $result['applicant_name']);
    $this->assertEquals('Jane Owner', $result['owner_name']);
    $this->assertEquals('ABC Surveying', $result['surveyor']);
    $this->assertEquals('789 Elm St, Springfield, KY 40423', $result['property_address']);
    $this->assertEquals('Commercial', $result['project_type']);
    $this->assertEquals('10000', $result['square_feet']);
    $this->assertEquals(0, $result['form_paid_bool']);
}

public function testExtractZoningPermitFormDataWithMinimalData()
{
    $post = [];
    
    $result = extractZoningPermitFormData($post);
    
    $this->assertIsArray($result);
    $this->assertNull($result['applicant_name']);
    $this->assertNull($result['property_address']);
    $this->assertEquals(0, $result['form_paid_bool']);
}

public function testExtractZoningPermitFormDataHandlesCorrectionFormId()
{
    $post = [
        'p_correction_form_id' => '999',
    ];
    
    $result = extractZoningPermitFormData($post);
    
    $this->assertEquals(999, $result['correction_form_id']);
    $this->assertIsInt($result['correction_form_id']);
}

public function testExtractZoningPermitFormDataAlwaysSetsFormPaidBoolToZero()
{
    $post1 = [];
    $result1 = extractZoningPermitFormData($post1);
    $this->assertEquals(0, $result1['form_paid_bool']);
    
    $post2 = ['form_paid_bool' => '1'];
    $result2 = extractZoningPermitFormData($post2);
    $this->assertEquals(0, $result2['form_paid_bool']);
}

public function testExtractZoningPermitFormDataInitializesFileFieldsAsNull()
{
    $post = [];
    
    $result = extractZoningPermitFormData($post);
    
    $this->assertNull($result['project_plans_file']);
    $this->assertNull($result['landscape_plans_file']);
    $this->assertNull($result['verification_file']);
    $this->assertNull($result['site_evaluation_file']);
    $this->assertNull($result['additional_docs_file']);
}

// ==================== validateZoningPermitData Tests ====================

public function testValidateZoningPermitDataWithValidData()
{
    $formData = [
        'applicant_name' => 'John Doe Construction',
        'property_address' => '789 Elm St',
        'applicant_email' => 'john@example.com',
        'applicant_phone' => '555-1234',
        'project_type' => 'Commercial',
        'square_feet' => '10000',
        'project_value' => '500000',
    ];
    
    $errors = validateZoningPermitData($formData);
    
    $this->assertEmpty($errors);
}

public function testValidateZoningPermitDataWithMissingRequired()
{
    $formData = [
        'applicant_name' => '',
        'property_address' => '',
    ];
    
    $errors = validateZoningPermitData($formData);
    
    $this->assertNotEmpty($errors);
    $this->assertContains('Applicant name is required', $errors);
    $this->assertContains('Property address is required', $errors);
}

public function testValidateZoningPermitDataWithInvalidPhone()
{
    $formData = [
        'applicant_name' => 'John Doe',
        'property_address' => '123 Main St',
        'applicant_phone' => 'not-a-phone',
    ];
    
    $errors = validateZoningPermitData($formData);
    
    $this->assertContains('Applicant phone format is invalid', $errors);
}

public function testValidateZoningPermitDataWithMultipleInvalidPhones()
{
    $formData = [
        'applicant_name' => 'John Doe',
        'property_address' => '123 Main St',
        'applicant_phone' => 'invalid',
        'owner_phone' => 'also-invalid',
        'applicant_cell' => 'wrong',
    ];
    
    $errors = validateZoningPermitData($formData);
    
    $this->assertContains('Applicant phone format is invalid', $errors);
    $this->assertContains('Owner phone format is invalid', $errors);
    $this->assertContains('Applicant cell format is invalid', $errors);
}

public function testValidateZoningPermitDataWithValidPhoneFormats()
{
    $validPhones = [
        '555-1234',
        '(555) 123-4567',
        '555.123.4567',
        '5551234567',
        '555 123 4567',
    ];
    
    foreach ($validPhones as $phone) {
        $formData = [
            'applicant_name' => 'John Doe',
            'property_address' => '123 Main St',
            'applicant_phone' => $phone,
        ];
        
        $errors = validateZoningPermitData($formData);
        $this->assertEmpty($errors, "Phone format '$phone' should be valid");
    }
}

public function testValidateZoningPermitDataWithInvalidEmail()
{
    $formData = [
        'applicant_name' => 'John Doe',
        'property_address' => '123 Main St',
        'applicant_email' => 'invalid-email',
    ];
    
    $errors = validateZoningPermitData($formData);
    
    $this->assertContains('Applicant email format is invalid', $errors);
}

public function testValidateZoningPermitDataWithMultipleInvalidEmails()
{
    $formData = [
        'applicant_name' => 'John Doe',
        'property_address' => '123 Main St',
        'applicant_email' => 'invalid',
        'owner_email' => 'also-invalid',
    ];
    
    $errors = validateZoningPermitData($formData);
    
    $this->assertContains('Applicant email format is invalid', $errors);
    $this->assertContains('Owner email format is invalid', $errors);
}

public function testValidateZoningPermitDataWithInvalidSquareFeet()
{
    $formData = [
        'applicant_name' => 'John Doe',
        'property_address' => '123 Main St',
        'square_feet' => 'not-a-number',
    ];
    
    $errors = validateZoningPermitData($formData);
    
    $this->assertContains('Square feet must be a valid number', $errors);
}

public function testValidateZoningPermitDataWithValidSquareFeetFormats()
{
    $validValues = ['1000', '5000.5', '12345'];
    
    foreach ($validValues as $value) {
        $formData = [
            'applicant_name' => 'John Doe',
            'property_address' => '123 Main St',
            'square_feet' => $value,
        ];
        
        $errors = validateZoningPermitData($formData);
        $this->assertEmpty($errors, "Square feet '$value' should be valid");
    }
}

public function testValidateZoningPermitDataWithInvalidProjectValue()
{
    $formData = [
        'applicant_name' => 'John Doe',
        'property_address' => '123 Main St',
        'project_value' => 'not-a-number',
    ];
    
    $errors = validateZoningPermitData($formData);
    
    $this->assertContains('Project value must be a valid number', $errors);
}

public function testValidateZoningPermitDataWithValidProjectValueFormats()
{
    $validValues = ['500000', '$500,000', '1000000.50', '$1,000,000.50'];
    
    foreach ($validValues as $value) {
        $formData = [
            'applicant_name' => 'John Doe',
            'property_address' => '123 Main St',
            'project_value' => $value,
        ];
        
        $errors = validateZoningPermitData($formData);
        $this->assertEmpty($errors, "Project value '$value' should be valid");
    }
}

public function testValidateZoningPermitDataWithInvalidProjectType()
{
    $formData = [
        'applicant_name' => 'John Doe',
        'property_address' => '123 Main St',
        'project_type' => 'Invalid Type',
    ];
    
    $errors = validateZoningPermitData($formData);
    
    $this->assertContains('Invalid project type selected', $errors);
}

public function testValidateZoningPermitDataWithValidProjectTypes()
{
    $validTypes = ['Multi-Family', 'Commercial', 'Industrial', 'Temporary Use', 'Parking/Display', 'Use Change'];
    
    foreach ($validTypes as $type) {
        $formData = [
            'applicant_name' => 'John Doe',
            'property_address' => '123 Main St',
            'project_type' => $type,
        ];
        
        $errors = validateZoningPermitData($formData);
        $this->assertEmpty($errors, "Project type '$type' should be valid");
    }
}

// ==================== processZoningPermitFileUploads Tests ====================

public function testProcessZoningPermitFileUploadsWithNoFiles()
{
    $files = [];
    
    $result = processZoningPermitFileUploads($files);
    
    $this->assertIsArray($result);
    $this->assertNull($result['project_plans_file']);
    $this->assertNull($result['landscape_plans_file']);
    $this->assertNull($result['verification_file']);
    $this->assertNull($result['site_evaluation_file']);
    $this->assertNull($result['additional_docs_file']);
}

public function testProcessZoningPermitFileUploadsWithUploadErrors()
{
    $files = [
        'project_plans' => ['error' => UPLOAD_ERR_NO_FILE],
        'landscape_plans' => ['error' => UPLOAD_ERR_INI_SIZE],
        'verification_file' => ['error' => UPLOAD_ERR_PARTIAL],
    ];
    
    $result = processZoningPermitFileUploads($files);
    
    $this->assertNull($result['project_plans_file']);
    $this->assertNull($result['landscape_plans_file']);
    $this->assertNull($result['verification_file']);
}

public function testProcessZoningPermitFileUploadsReturnsCorrectKeys()
{
    $files = [];
    
    $result = processZoningPermitFileUploads($files);
    
    $this->assertArrayHasKey('project_plans_file', $result);
    $this->assertArrayHasKey('landscape_plans_file', $result);
    $this->assertArrayHasKey('verification_file', $result);
    $this->assertArrayHasKey('site_evaluation_file', $result);
    $this->assertArrayHasKey('additional_docs_file', $result);
}

// ==================== insertZoningPermitApplication Tests ====================

public function testInsertZoningPermitApplicationWithPrepareFailure()
{
    $fakeConn = new FakeMysqliConnection(false, 'Prepare error message');
    
    $formData = extractZoningPermitFormData([]);
    $result = insertZoningPermitApplication($fakeConn, $formData);
    
    $this->assertFalse($result['success']);
    $this->assertStringContainsString('Prepare failed', $result['message']);
    $this->assertNull($result['form_id']);
}

public function testInsertZoningPermitApplicationReturnsCorrectStructure()
{
    $fakeConn = new FakeMysqliConnection(false, 'Test error');
    
    $formData = extractZoningPermitFormData([]);
    $result = insertZoningPermitApplication($fakeConn, $formData);
    
    $this->assertIsArray($result);
    $this->assertArrayHasKey('success', $result);
    $this->assertArrayHasKey('message', $result);
    $this->assertArrayHasKey('form_id', $result);
}

// ==================== Full Workflow Tests ====================

public function testZoningPermitFullWorkflowWithValidData()
{
    $post = [
        'applicant_name' => 'John Doe Construction',
        'property_address' => '789 Elm St',
        'applicant_email' => 'john@example.com',
        'applicant_phone' => '555-1234',
        'project_type' => 'Commercial',
        'square_feet' => '10000',
        'project_value' => '500000',
    ];
    
    $formData = extractZoningPermitFormData($post);
    $errors = validateZoningPermitData($formData);
    
    $this->assertEmpty($errors);
    $this->assertEquals('John Doe Construction', $formData['applicant_name']);
    $this->assertEquals('Commercial', $formData['project_type']);
}

public function testZoningPermitFullWorkflowWithInvalidData()
{
    $post = [
        'applicant_name' => '',
        'property_address' => '',
        'applicant_email' => 'invalid-email',
        'square_feet' => 'not-a-number',
        'project_type' => 'Invalid Type',
    ];
    
    $formData = extractZoningPermitFormData($post);
    $errors = validateZoningPermitData($formData);
    
    $this->assertNotEmpty($errors);
    $this->assertGreaterThan(3, count($errors));
}

public function testZoningPermitFullWorkflowWithFilesAndValidation()
{
    $post = [
        'applicant_name' => 'John Doe Construction',
        'property_address' => '789 Elm St',
    ];
    
    $files = [
        'project_plans' => ['error' => UPLOAD_ERR_NO_FILE],
        'landscape_plans' => ['error' => UPLOAD_ERR_NO_FILE],
    ];
    
    $formData = extractZoningPermitFormData($post, $files);
    $errors = validateZoningPermitData($formData);
    $uploadedFiles = processZoningPermitFileUploads($files);
    
    $this->assertEmpty($errors);
    $this->assertNull($uploadedFiles['project_plans_file']);
    $this->assertNull($uploadedFiles['landscape_plans_file']);
}

public function testZoningPermitFullWorkflowMergesUploadedFiles()
{
    $post = [
        'applicant_name' => 'John Doe Construction',
        'property_address' => '789 Elm St',
    ];
    
    $formData = extractZoningPermitFormData($post);
    
    // Simulate uploaded files
    $uploadedFiles = [
        'project_plans_file' => 'uploads/test_plan.pdf',
        'landscape_plans_file' => null,
        'verification_file' => null,
        'site_evaluation_file' => null,
        'additional_docs_file' => null,
    ];
    
    $formData = array_merge($formData, $uploadedFiles);
    
    $this->assertEquals('uploads/test_plan.pdf', $formData['project_plans_file']);
    $this->assertNull($formData['landscape_plans_file']);
}

public function testZoningPermitFormDataHandlesAllProfessionalContacts()
{
    $post = [
        'applicant_name' => 'John Doe',
        'property_address' => '123 Main St',
        'surveyor' => 'ABC Surveying',
        'contractor' => 'XYZ Contractors',
        'architect' => 'Smith Architecture',
        'landscape_architect' => 'Green Landscapes',
    ];
    
    $formData = extractZoningPermitFormData($post);
    $errors = validateZoningPermitData($formData);
    
    $this->assertEmpty($errors);
    $this->assertEquals('ABC Surveying', $formData['surveyor']);
    $this->assertEquals('XYZ Contractors', $formData['contractor']);
    $this->assertEquals('Smith Architecture', $formData['architect']);
    $this->assertEquals('Green Landscapes', $formData['landscape_architect']);
}

}