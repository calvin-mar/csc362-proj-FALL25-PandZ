<?php

function result_to_html_table($result) {
        $qryres = $result->fetch_all();
        $n_rows = $result->num_rows;
        $n_cols = $result->field_count;
        $fields = $result->fetch_fields();
        ?>
        <!-- Description of table - - - - - - - - - - - - - - - - - - - - -->
        <!-- <p>This table has <?php //echo $n_rows; ?> and <?php //echo $n_cols; ?> columns.</p> -->
        
        <!-- Begin header - - - - - - - - - - - - - - - - - - - - -->
        <!-- Using default action (this page). -->
        <table>
        <thead>
        <tr>
        <?php for ($i=0; $i<$n_cols; $i++){ ?>
            <td><b><?php echo $fields[$i]->name; ?></b></td>
        <?php } ?>
        </tr>
        </thead>
        
        <!-- Begin body - - - - - - - - - - - - - - - - - - - - - -->
        <tbody>
        <?php for ($i=0; $i<$n_rows; $i++){ ?>
            <?php $id = $qryres[$i][0]; ?>
            <tr>     
            <?php for($j=0; $j<$n_cols; $j++){ ?>
                <td><?php echo $qryres[$i][$j]; ?></td>
            <?php } ?>
            </tr>
        <?php } ?>
        </tbody></table>
<?php }