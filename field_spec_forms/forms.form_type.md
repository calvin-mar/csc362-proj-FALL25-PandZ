# Field Specifications

## General Elements

| Field                 | Value                             |
|-----------------------|-----------------------------------|
| Field Name            | form_type                         |
| Parent Table          | forms                             |
| Alias(es)             | N/A                               |
| Specification Type    | [ ] Unique                        |
|                       | [X] Generic                       |
|                       | [ ] Replica                       |
|                       |                                   |
| Source Specification  | form_types.form_type              |
| Shared By             | All applications tables           |
| Description           | Defines type of form being submitted |


## Physical Elements

| Field                 | Value                             |
|-----------------------|-----------------------------------|
| Data Type             | VARCHAR                           |
| Length                | 50                                |
| Decimal Places        | N/A                               |
| Character Support     | [X] Letters (A-Z)                 |
|                       | [X] Numbers (0-9)                 |
|                       | [X] Keyboard (.,/$#%)             |
|                       | [ ] Special (©®™Σπ)               |


## Logical Elements

| Field                 | Value                             |
|-----------------------|-----------------------------------|
| Key Type              | [ ] Non                           |
|                       | [ ] Primary                       |   
|                       | [X] Foreign                       |
|                       | [ ] Alternate                     |
|                       |                                   |
| Key Structure         | [X] Simple                        |
|                       | [ ] Composite                     |
|                       |                                   |
| Uniqueness            | [X] Non-unique                    |
|                       | [ ] Unique                        |
|                       |                                   |
| Null Support          | [ ] Nulls OK                      |
|                       | [X] No nulls                      |
|                       |                                   |
| Values Entered By     | [X] User                          |
|                       | [ ] System                        |
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
Each form type should correspond to a planning and zoning form used in DB.