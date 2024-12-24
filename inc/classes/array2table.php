<?php
class Array2table {
    public function getTable($data) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="de">
      
            <div class="container mt-5">
                <h1 class="mb-4">Benutzerliste</h1>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <?php
                            $columns = array();
                            foreach ($data as $row) {
                                foreach ($row as $key => $value) {
                                    if (!is_int($key) && !in_array($key, $columns)) {
                                        $columns[] = $key;
                                    }
                                }
                            }
                            foreach ($columns as $column) {
                                echo "<th>" . htmlspecialchars($column) . "</th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($data as $row) {
                            echo "<tr>";
                            foreach ($columns as $column) {
                                echo "<td>" . htmlspecialchars($row[$column]) . "</td>";
                            }
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
?>
