<head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<form method="post" enctype="multipart/form-data">
    <input type="text" name="nauczyciel" value="mgr inż. A. Sandomierski" placeholder="Nauczyciel"/>
    <input type="date" name="data" id="data_input" />
    <p><button type="submit" name="submit">Submit</button></p>
</form>
<script type="text/javascript">
  var postData = "<?php echo $_POST["data"]; ?>";

  var data = moment(postData);
  var formatedData = data.format("YYYY.MM.DD")
document.getElementById("data_input").value = formatedData; 
</script>

<pre>
<?php
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
if(count($_POST)>0){

$inputFileType = 'Xls';
// $inputFileName = './plan3-IT.xls';
$inputFilesNames = ['./plan1-IT.xls', './plan2-IT.xls', './plan3-IT.xls', './plan4-IT.xls'];
$class_data = [];
foreach ($inputFilesNames as $inputFileName)
{
    $reader = IOFactory::createReader($inputFileType);
    $spreadsheet = $reader->load($inputFileName);
    $sheetData = $spreadsheet->getActiveSheet()
        ->toArray(null, true, true, true);
    $title = $sheetData[1]["A"];
    $title = explode("Kierunek: ", $title) [1];
    $title = str_replace("     ", "", explode("rok", $title) [0]);
    $title = str_replace("    ", "", explode("rok", $title) [0]);
    $title = str_replace("   ", "", explode("rok", $title) [0]);
    $title = str_replace("  ", "", explode("rok", $title) [0]);
    $title = str_replace(" ", "", explode("rok", $title) [0]);
    echo $title;
    $time_range = [];
    foreach ($sheetData[2] as $time_row => $value) if ($time_row !== "A" && $time_row !== "B") $time_range[$time_row] = $value;
    $was_two_groups = false;
    $next_is_empty = false;
    $init = false;
    $data = "";
    foreach (array_slice($sheetData, 2) as $index => $data_col)
    {
        $row_id = (intval($index) + 3);
        $two_groups = false;
        if (isset($data_col["B"]))
        {
            $two_groups = true;
            $was_two_groups = $init && ($was_two_groups ? false : true);
            $init = true;
        }
        $exploded_data = explode(" ", $data_col["A"]);
        if (isset($exploded_data[0]) && count(explode("\n", $exploded_data[0])) > 1)
        {
            $exploded_data = explode("\n", $exploded_data[0]);
            $data = $exploded_data[1];
        }
        $data = str_replace("\n", "", $data);
        $data = str_replace("..", ".", $data);
        if (isset($exploded_data[1])) $data = $exploded_data[1];
        if (isset($exploded_data[1]) && preg_match("^([0]?[1-9]|[1|2][0-9]|[3][0|1])(\.{2}|\.)([0]?[1-9]|[1][0-2])[./-]([0-9]{4}|[0-9]{2})$^", $exploded_data[1]) || $was_two_groups)
        {
            foreach ($data_col as $column_id => $column)
            {
                if ($column_id === "B" && $row_id > 4)
                {
                    $cell = $spreadsheet->getActiveSheet()
                        ->getCell($column_id . $row_id);
                    if (isset(explode("\n", $cell->getValue()) [1])) $grupa = explode("\n", $cell->getValue()) [1];
                    else $grupa = explode("\n", $cell->getValue()) [0];
                }
                else if ($column !== "" && $column_id !== "A" && $column_id !== "B" && $row_id > 4)
                {
                    $cell = $spreadsheet->getActiveSheet()
                        ->getCell($column_id . $row_id);
                    foreach ($spreadsheet->getActiveSheet()
                        ->getMergeCells() as $id => $cells)
                    {
                        if ($cell->isInRange($cells))
                        {
                            $exploded_cells = (explode(":", $cells));
                            $first_cell_row_id = substr($exploded_cells[0], 1);
                            $second_cell_row_id = substr($exploded_cells[1], 1);
                            $first_cell_col_id = ($exploded_cells[0][0]);
                            $second_cell_col_id = ($exploded_cells[1][0]);
                            $class_hours = $first_cell_col_id . ":" . $second_cell_col_id;
                            if ($first_cell_row_id !== $second_cell_row_id && $cell->getValue() !== null)
                            {
                                $data = str_replace("\n", "", $data);
                                $data = str_replace("..", ".", $data);
                                if (is_string($cell->getValue()))
                                {
                                    $class_data[$title][$data][$grupa][$id]["value"] = $cell->getValue();
                                    $teacher = explode("\n", $cell->getValue());
                                    $class_data[$title][$data][$grupa][$id]["teacher"] = $teacher;
                                    if (count($teacher) > 3 && strpos($teacher[count($teacher) - 1], 'godz') !== false || strpos($teacher[count($teacher) - 1], 'on-line') !== false || strpos($teacher[count($teacher) - 1], 'lab') !== false || strpos($teacher[count($teacher) - 1], 'sala') !== false || strpos($teacher[count($teacher) - 1], 'przeniesione') !== false)
                                    {
                                        if (isset($teacher[count($teacher) - 2]))
                                        {
                                            $class_data[$title][$data][$grupa][$id]["teacher"] = ($teacher[count($teacher) - 2]);
                                            $class_data[$title][$data][$grupa][$id]["teacher"] = str_replace("..", ".", $class_data[$title][$data][$grupa][$id]["teacher"]);
                                        }
                                        else
                                        {
                                            $teacher = ("NIE MOGE WYDOBYC NAUCZYCIELA");
                                            $class_data[$title][$data][$grupa][$id]["teacher"] = $teacher;
                                            $class_data[$title][$data][$grupa][$id]["teacher"] = str_replace("..", ".", $class_data[$title][$data][$grupa][$id]["teacher"]);
                                        }
                                    }
                                    else if (is_array($teacher))
                                    {
                                        if (strpos($teacher[1], 'mgr') !== false || strpos($teacher[1], 'prof') !== false || strpos($teacher[1], 'dr') !== false || strpos($teacher[1], 'inz') !== false || strpos($teacher[1], 'inż') !== false)
                                        {
                                            $class_data[$title][$data][$grupa][$id]["teacher"] = $teacher[1];
                                            $class_data[$title][$data][$grupa][$id]["teacher"] = str_replace("..", ".", $class_data[$title][$data][$grupa][$id]["teacher"]);
                                        }
                                        else
                                        {
                                            $class_data[$title][$data][$grupa][$id]["teacher"] = $teacher[2];
                                            $class_data[$title][$data][$grupa][$id]["teacher"] = str_replace("..", ".", $class_data[$title][$data][$grupa][$id]["teacher"]);
                                        }
                                    }
                                    else
                                    {
                                        $teacher = ($teacher[count($teacher) - 1]);
                                        $class_data[$title][$data][$grupa][$id]["teacher"] = $teacher;
                                        $class_data[$title][$data][$grupa][$id]["teacher"] = str_replace("..", ".", $class_data[$title][$data][$grupa][$id]["teacher"]);
                                    }
                                }
                                else if (is_array($teacher))
                                {
                                    if (strpos($teacher[1], 'mgr') !== false || strpos($teacher[1], 'prof') !== false || strpos($teacher[1], 'dr') !== false || strpos($teacher[1], 'inz') !== false || strpos($teacher[1], 'inż') !== false)
                                    {
                                        $class_data[$title][$data][$grupa][$id]["teacher"] = $teacher[1];
                                        $class_data[$title][$data][$grupa][$id]["teacher"] = str_replace("..", ".", $class_data[$title][$data][$grupa][$id]["teacher"]);
                                    }
                                    else
                                    {
                                        $class_data[$title][$data][$grupa][$id]["teacher"] = $teacher[2];
                                        $class_data[$title][$data][$grupa][$id]["teacher"] = str_replace("..", ".", $class_data[$title][$data][$grupa][$id]["teacher"]);
                                    }
                                }
                                else
                                {
                                    $class_data[$title][$data][$grupa][$id]["value"] = ($cell->getValue()
                                        ->getPlainText());
                                    $teacher = explode("\n", $cell->getValue());
                                    $class_data[$title][$data][$grupa][$id]["teacher"] = $teacher;
                                    if (count($teacher) > 3 && strpos($teacher[count($teacher) - 1], 'godz') !== false || strpos($teacher[count($teacher) - 1], 'sala') !== false || strpos($teacher[count($teacher) - 1], 'on-line') !== false || strpos($teacher[count($teacher) - 1], 'lab') !== false || strpos($teacher[count($teacher) - 1], 'sala') !== false || strpos($teacher[count($teacher) - 1], 'przeniesione') !== false)
                                    {
                                        $class_data[$title][$data][$grupa][$id]["teacher"] = ($teacher[count($teacher) - 2]);
                                        $class_data[$title][$data][$grupa][$id]["teacher"] = str_replace("..", ".", $class_data[$title][$data][$grupa][$id]["teacher"]);
                                    }
                                }
                                $class_data[$title][$data][$grupa][$id]["both_groups"] = true;
                            }
                            else if ($cell->getValue() !== null)
                            {
                                if (is_string($cell->getValue())) $class_data[$title][$data][$grupa][$id]["value"] = $cell->getValue();
                                else $class_data[$title][$data][$grupa][$id]["value"] = ($cell->getValue()
                                    ->getPlainText());
                                $class_data[$title][$data][$grupa][$id]["both_groups"] = false;
                                $teacher = explode("\n", $cell->getValue());
                                $class_data[$title][$data][$grupa][$id]["teacher"] = $teacher;
                                if (count($teacher) > 3 && strpos($teacher[count($teacher) - 1], 'godz') !== false || strpos($teacher[count($teacher) - 1], 'przeniesione') !== false || strpos($teacher[count($teacher) - 1], 'on-line') !== false || strpos($teacher[count($teacher) - 1], 'lab') !== false || strpos($teacher[count($teacher) - 1], 'sala') !== false)
                                {
                                    $class_data[$title][$data][$grupa][$id]["teacher"] = ($teacher[count($teacher) - 2]);
                                }
                                if (is_array($class_data[$title][$data][$grupa][$id]["teacher"])) $class_data[$title][$data][$grupa][$id]["teacher"] = $class_data[$title][$data][$grupa][$id]["teacher"][2];
                                $class_data[$title][$data][$grupa][$id]["teacher"] = str_replace("..", ".", $class_data[$title][$data][$grupa][$id]["teacher"]);
                                $class_data[$title][$data][$grupa][$id]["class_hours"] = $class_hours;
                            }
                        }
                    }
                }

            }
        }
        else
        {
            $next_is_empty = $next_is_empty ? false : true;
        }
    }
}

array_filter($class_data, function ($e)
{
    foreach ($e as $data_id => $data)
    {
        foreach ($data as $d_id => $d)
        {
            foreach ($d as $g)
            {
                foreach ($g as $id => $val)
                {
                    if ($id === "teacher" && $val === $_POST["nauczyciel"] && $data_id === date("d.m.Y", strtotime($_POST["data"])))
                    {
                        var_dump(["data" => $data_id, "grupa/specjalizacja" => $d_id, "props"=> $g]);
                        echo "\n";

                    }
                }
            }
        }
    }
});
}

?>
  </pre>
