<?php
/**
 * Renders a table grid of label/value pairs.
 *
 * Expected variables:
 * - $array: associative array label => value
 */
?>
<table class="table table-bordered table-striped table-responsive">
    <tbody>
        <tr>
            <?php
            $column = 0;
            $maxColumn = 3;
            foreach ($array as $key => $value) {
                if (!empty($value)) {
                    // Defensive rendering: some values might be accidentally arrays/objects.
                    // Convert them into a readable scalar to avoid "Array to string conversion" warnings.
                    if (is_array($value)) {
                        $value = implode(', ', array_map(static function ($v) {
                            if ($v === null) {
                                return '';
                            }
                            if (is_scalar($v)) {
                                return (string)$v;
                            }
                            return json_encode($v);
                        }, $value));
                    } elseif (is_object($value) && !method_exists($value, '__toString')) {
                        $value = json_encode($value);
                    }

                    $column++;
                    ?>
                    <th>
                        <?= $key ?>
                    </th>
                    <td>
                        <?php
                        if ($key === 'Page Link') {
                            $href = is_scalar($value) ? (string)$value : '';
                            echo $href !== ''
                                ? "<a href='$href'>Page Link</a>"
                                : '';
                        } else {
                            echo is_scalar($value) ? $value : '';
                        }
                        ?>
                    </td>
                    <?php
                    if ($column >= $maxColumn) {
                        ?>
                        </tr>
                        <tr>
                        <?php
                        $column = 0;
                    }
                }
            }
            if ($column > 0 && $column <= $maxColumn) {
                for ($i = $maxColumn - $column; $i > 0; $i--) {
                    ?>
                    <th>&nbsp;</th>
                    <td>&nbsp;</td>
                    <?php
                }
            }
            ?>
        </tr>
    </tbody>
</table>

