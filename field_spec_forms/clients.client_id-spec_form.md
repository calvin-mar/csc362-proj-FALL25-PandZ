# Field Specifications

## General Elements

| Field                 | Value                             |
|-----------------------|-----------------------------------|
| Field Name            | client_id                         |
| Parent Table          | clients                           |
| Alias(es)             | N/A                               |
| Specification Type    | [X] Unique                        |
|                       | [ ] Generic                       |
|                       | [ ] Replica                       |
|                       |                                   |
| Source Specification  | None                              |
| Shared By             | incomplete_client_forms           |
|                       | client_forms                      |
| Description           | This field identifies a specific  |
|                       | client matching with their forms  |


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
This field keeps track of a client so that they are properly matched with forms that have been submitted
and to see "saved" forms that want to be worked on at a later date.