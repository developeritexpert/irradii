<?php

namespace app\components;

use Yii;

class AlertsMessagesProcessor
{
    private $rowArray = [];

    public function processAlertsMessagesFile($path)
    {
        $this->readFileToArray($path);
        $this->deleteMessagesInTable();
        $this->saveArrayToDB();
    }

    private function readFileToArray($path)
    {
        $row = 0;
        if (($handle = fopen($path, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                if ($row != 0) {
                    $this->rowArray[] = $data;
                }
                $row++;
            }
            fclose($handle);
        }
    }

    private function saveArrayToDB()
    {
        foreach ($this->rowArray as $row) {
            Yii::$app->db->createCommand()->insert('tbl_alerts_scheduled_messages', [
                'date' => date('Y-m-d', strtotime($row[0])),
                'message_1' => $row[1] ?? '',
                'message_2' => $row[2] ?? '',
                'message_3' => $row[3] ?? '',
            ])->execute();
        }
    }

    public function deleteMessagesInTable()
    {
        Yii::$app->db->createCommand()->delete('tbl_alerts_scheduled_messages')->execute();
    }
}
