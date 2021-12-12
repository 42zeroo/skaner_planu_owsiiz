<head>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
<style>
 *{
    font-family: 'Poppins', sans-serif;
 }
</style>
</head>
<form method="post" enctype="multipart/form-data">
    <input type="text" name="nauczyciel" value="mgr inż. A. Sandomierski" placeholder="Nauczyciel"/>
    <input type="date" name="data" id="data_input" />
    <p><button type="submit" name="submit">Submit</button></p>
</form>

<?php
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
if (count($_POST) > 0)
{

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
        // echo $title."\n";
        $time_range = [];
        $hours = ["C" => ["from" => "7.10", "to" => "7.55"], "D" => ["from" => "8.00", "to" => "8.45"], "E" => ["from" => "8.50", "to" => "9.35"], "F" => ["from" => "9.40", "to" => "10.25"], "G" => ["from" => "10.30", "to" => "11.15"], "H" => ["from" => "11.20", "to" => "12.05"], "I" => ["from" => "12.10", "to" => "12.55"], "J" => ["from" => "13.00", "to" => "13.45"], "K" => ["from" => "14.15", "to" => "15.00"], "L" => ["from" => "15.05", "to" => "15.50"], "M" => ["from" => "15.55", "to" => "16.40"], "N" => ["from" => "16.45", "to" => "17.30"], "O" => ["from" => "17.35", "to" => "18.20"], "P" => ["from" => "18.25", "to" => "19.10"], "Q" => ["from" => "19.15", "to" => "20.00"], "R" => ["from" => "20.05", "to" => "20.50"], "S" => ["from" => "20.55", "to" => "21.40"], "T" => ["from" => "21.45", "to" => "22.30"]];
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
                                $class_hours = $hours[$first_cell_col_id]['from'] . " - " . $hours[$second_cell_col_id]['to'];
                                if ($first_cell_row_id !== $second_cell_row_id && $cell->getValue() !== null)
                                {
                                    $data = str_replace("\n", "", $data);
                                    $data = str_replace("..", ".", $data);
                                    // if($data === "12.12.2021" && $title === "InformatykaII" && !is_string($cell->getValue())) var_dump(($cell->getValue()->getPlainText()));
                                    if (is_string($cell->getValue()))
                                    {
                                        $class_data[$title][$data][$grupa][$id]["value"] = $cell->getValue();
                                        $teacher = explode("\n", $cell->getValue());
                                        $class_data[$title][$data][$grupa][$id]["teacher"] = $teacher;
                                        // var_dump($teacher);
                                        foreach ($teacher as $t)
                                        {
                                            if (strpos($t, 'mgr') !== false || strpos($teacher[1], 'prof') !== false || strpos($t, 'dr') !== false || strpos($t, 'inz') !== false || strpos($t, 'inż') !== false)
                                            {
                                                $class_data[$title][$data][$grupa][$id]["teacher"] = $t;
                                                $class_data[$title][$data][$grupa][$id]["teacher"] = str_replace("..", ".", $class_data[$title][$data][$grupa][$id]["teacher"]);
                                            }
                                            else
                                            {
                                                $class_data[$title][$data][$grupa][$id]["teacher"] = "NIE MOGE WYDOBYC NAUCZYCIELA";
                                            }
                                        }
                                    }
                                    if (!is_string($cell->getValue()))
                                    {
                                        $class_data[$title][$data][$grupa][$id]["value"] = $cell->getValue()
                                            ->getPlainText();
                                        $teacher = explode("\n", $cell->getValue()
                                            ->getPlainText());
                                        $class_data[$title][$data][$grupa][$id]["teacher"] = $teacher;
                                        if (count($teacher) > 3 && strpos($teacher[count($teacher) - 1], 'godz') !== false || strpos($teacher[count($teacher) - 1], 'on-line') !== false || strpos($teacher[count($teacher) - 1], 'lab') !== false || strpos($teacher[count($teacher) - 1], 'sala') !== false || strpos($teacher[count($teacher) - 1], 'przeniesione') !== false || strpos($teacher[count($teacher) - 1], 'mgr') !== false)
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
                                            foreach ($teacher as $t)
                                            {
                                                if (strpos($t, 'mgr') !== false || strpos($teacher[1], 'prof') !== false || strpos($t, 'dr') !== false || strpos($t, 'inz') !== false || strpos($t, 'inż') !== false)
                                                {
                                                    $class_data[$title][$data][$grupa][$id]["teacher"] = $t;
                                                    $class_data[$title][$data][$grupa][$id]["teacher"] = str_replace("..", ".", $class_data[$title][$data][$grupa][$id]["teacher"]);
                                                }
                                                else
                                                {
                                                    $class_data[$title][$data][$grupa][$id]["teacher"] = "NIE MOGE WYDOBYC NAUCZYCIELA";
                                                }
                                            }

                                        }
                                        else
                                        {
                                            $teacher = ($teacher[count($teacher) - 1]);
                                            $class_data[$title][$data][$grupa][$id]["teacher"] = $teacher;
                                            $class_data[$title][$data][$grupa][$id]["teacher"] = str_replace("..", ".", $class_data[$title][$data][$grupa][$id]["teacher"]);
                                        }
                                    }

                                    foreach ($teacher as $t)
                                    {
                                        if (strpos($t, 'mgr') !== false || strpos($teacher[1], 'prof') !== false || strpos($t, 'dr') !== false || strpos($t, 'inz') !== false || strpos($t, 'inż') !== false)
                                        {
                                            $class_data[$title][$data][$grupa][$id]["teacher"] = $t;
                                            $class_data[$title][$data][$grupa][$id]["teacher"] = str_replace("..", ".", $class_data[$title][$data][$grupa][$id]["teacher"]);
                                        }
                                        else
                                        {
                                            $class_data[$title][$data][$grupa][$id]["teacher"] = "NIE MOGE WYDOBYC NAUCZYCIELA";
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
                                }
                                $class_data[$title][$data][$grupa][$id]["class_hours"] = $class_hours;
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
}
$class_names = array_keys($class_data);
$whole_data = $class_data;
$index = 0;
foreach ($whole_data as $class_name => $class_data)
{
    echo ("\n<h2>" . $class_name . "</h2>");
    foreach ($class_data as $date_key => $date_data)
    {
        if (isset($_POST["data"]) && str_replace("\n", "", $date_key) === date("d.m.Y", strtotime($_POST["data"])))
        {
            foreach ($date_data as $grupa_key => $grupa_data)
            {
                foreach ($grupa_data as $id => $details)
                {
                    if (isset($details["teacher"]) && $details["teacher"] === $_POST["nauczyciel"])
                    {
                        echo (str_replace("\n", "", $date_key) . "<br/>");
                        echo "<b>Val</b>: " . $details["value"] . "<br/>";
                        echo "<b>Wykladowca</b>: " . $details["teacher"] . "<br/>";
                        echo "<b>Czy obie grupy</b>: " . ($details["both_groups"] ? "TAK" : 'NIE') . "<br/>";
                        echo "<b>Godziny</b>: " . $details["class_hours"] . "<br/>";
                    }
                }
            }
        }

    }
}
?>
