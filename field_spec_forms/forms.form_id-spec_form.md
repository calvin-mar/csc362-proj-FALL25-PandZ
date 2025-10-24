# Field Specifications

## General Elements

| Field                 | Value                              |
|-----------------------|------------------------------------|
| Field Name            |  form_id                           |
| Parent Table          |  forms                             |
| Alias(es)             |  N/A                               |
| Specification Type    | [X] Unique                         |
|                       | [ ] Generic                        |
|                       | [ ] Replica                        |
|                       |                                    |
| Source Specification  |  None                              |
| Shared By             | incomplete_client_forms,           |
|                       | client_forms,type_one_forms        |
|                       | hearing_forms, technical_forms     |
|                       | all sub tables forms               |
| Description           | This is the unique identifier for  |
|                       | a given form. It is used to keep   |
|                       | track of a form across the database|


## Physical Elements

| Field                 | Value                             |
|-----------------------|-----------------------------------|
| Data Type             | INT                               |
| Length                | N/A                               |
| Decimal Places        | N/A                               |
| Character Support     | [ ] Letters (A-Z)                 |
|                       | [X] Numbers (0-9)                 |
|                       | [ ] Keyboard (.,/$#%)             |
|                       | [ ] Special (©®™Σπ)               |


## Logical Elements

| Field                 | Value                             |
|-----------------------|-----------------------------------|
| Key Type              | [ ] Non                           |
|                       | [X] Primary                       |   
|                       | [ ] Foreign                       |
|                       | [ ] Alternate                     |
|                       |                                   |
| Key Structure         | [X] Simple                        |
|                       | [ ] Composite                     |
|                       |                                   |
| Uniqueness            | [ ] Non-unique                    |
|                       | [X] Unique                        |
|                       |                                   |
| Null Support          | [ ] Nulls OK                      |
|                       | [X] No nulls                      |
|                       |                                   |
| Values Entered By     | [ ] User                          |
|                       | [X] System                        |
|                       |                                   |
| Required Value        | [ ] No                            |
|                       | [X] Yes                           |
|                       |                                   |
| Range of Values       |                                   |
| Edit Rule             | [ ] Enter now, edits allowed      |
|                       | [X] Enter now, edits not allowed  |
|                       | [ ] Enter later, edits allowed    |
|                       | [ ] Enter later, edits not allowed|
|                       | [ ] Not determined at this time   |

## Notes
This field is highly important to be kept consistent across tables. 
As a foreign key it can be found in numerous different locations and it must be kept intact.
This field organizes the database around it.