# Field Specifications

## General Elements

| Field                 | Value                             |
|-----------------------|-----------------------------------|
| Field Name            | neighbor_id                       |
| Parent Table          | adjacent_neighbors                |
| Alias(es)             | N/A                               |
| Specification Type    | [ ] Unique                        |
|                       | [ ] Generic                       |
|                       | [X] Replica                       |
|                       |                                   |
| Source Specification  | apof_neigbors.neighbor_id         |
| Shared By             | apof_neighbors                    |
| Description           | Foreign Key that links adjacent neighbor record to its neighbor in apof_neighbors |


## Physical Elements

| Field                 | Value                             |
|-----------------------|-----------------------------------|
| Data Type             | INT                               |
| Length                |                                   |
| Decimal Places        | 0                                 |
| Character Support     | [ ] Letters (A-Z)                 |
|                       | [X] Numbers (0-9)                 |
|                       | [ ] Keyboard (.,/$#%)             |
|                       | [ ] Special (©®™Σπ)               |


## Logical Elements

| Field                 | Value                             |
|-----------------------|-----------------------------------|
| Key Type              | [ ] Non                           |
|                       | [ ] Primary                       |   
|                       | [X] Foreign                       |
|                       | [ ] Alternate                     |
|                       |                                   |
| Key Structure         | [ ] Simple                        |
|                       | [X] Composite                     |
|                       |                                   |
| Uniqueness            | [X] Non-unique                    |
|                       | [ ] Unique                        |
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