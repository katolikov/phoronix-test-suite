<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2009 - 2014, Phoronix Media
	Copyright (C) 2009 - 2014, Michael Larabel

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

$day_of_week_int = date('N') - 1;
echo 2222;
$stmt = phoromatic_server::$db->prepare('SELECT * FROM phoromatic_schedules WHERE AccountID = :account_id AND Status = 1 AND (SELECT COUNT(*) FROM phoromatic_schedules_tests WHERE AccountID = :account_id AND ScheduleID = phoromatic_schedules.ScheduleID) > 0');
$stmt->bindValue(':account_id', $_SESSION['AccountID']);
$stmt->bindValue(':schedule_id', $PATH[0]);
$result = $stmt->execute();
while($row = $result->fetchArray())
	echo $row['Title'];

return;

$result = mysql_query("SELECT Title, Description, ScheduleID, LastModifiedBy, SetContextPreInstall, SetContextPreRun FROM schedules WHERE AccountID = '" . AID . "' AND Status = 'ACTIVE' AND (SELECT COUNT(Test) FROM schedules_tests WHERE AccountID = '" . AID . "' AND ScheduleID = schedules.ScheduleID LIMIT 1) NOT LIKE '0' AND (SELECT COUNT(SystemID) FROM schedules_systems WHERE AccountID = '" . AID . "' AND ScheduleID = schedules.ScheduleID AND (SystemID = '" . SID . "' OR SystemID = '0') LIMIT 1) NOT LIKE '0' AND
(
(SUBSTRING(ActiveOn, DAYOFWEEK(NOW()), 1) = '1' AND RunAt <= CURRENT_TIME() AND '" . SID . "' NOT IN (SELECT SystemID FROM test_runs WHERE DATE(UploadTime) = CURRENT_DATE() AND TIME(UploadTime) > schedules.RunAt AND ScheduleID = schedules.ScheduleID))
 OR
((SELECT COUNT(TriggerString) FROM schedules_triggered WHERE AccountID = '" . AID . "' AND ScheduleID = schedules.ScheduleID AND TriggeredOn > DATE_SUB(NOW(), INTERVAL 1 DAY) AND '" . SID . "' NOT IN (SELECT SystemID FROM test_runs WHERE TriggerString = schedules_triggered.TriggerString AND ScheduleID = schedules.ScheduleID)) NOT LIKE '0')
)

 LIMIT 1");

$xml_writer = new nye_XmlWriter();
$xml_writer->addXmlNode('PhoronixTestSuite/Phoromatic/General/SystemName', SYSTEM_NAME);

if(mysql_num_rows($result) == 1)
{
	$record = mysql_fetch_object($result);
	mysql_free_result($result);
	$schedule_id = $record->ScheduleID;

	$result = mysql_query("SELECT TriggerString FROM schedules_triggered WHERE AccountID = '" . AID . "' AND ScheduleID = '" . $schedule_id . "' AND TriggeredOn > DATE_SUB(NOW(), INTERVAL 1 DAY) AND '" . SID . "' NOT IN (SELECT SystemID FROM test_runs WHERE TriggerString = schedules_triggered.TriggerString AND ScheduleID = '" . $schedule_id . "') ORDER BY TriggeredOn ASC LIMIT 1");

	if(mysql_num_rows($result) == 1)
	{
		$record_trigger = mysql_fetch_object($result);
		$trigger_string = $record_trigger->TriggerString;
	}
	else
	{
		$trigger_string = date('Y-m-d');
	}
	mysql_free_result($result);

	$xml_writer->addXmlNode('PhoronixTestSuite/Phoromatic/General/Response', 'benchmark');
	$xml_writer->addXmlNode('PhoronixTestSuite/Phoromatic/General/UploadToGlobal', (pts_rmm_get_settings_value("UploadResultsToGlobal") == 1 ? "TRUE" : "FALSE"));
	$xml_writer->addXmlNode('PhoronixTestSuite/Phoromatic/General/ArchiveResultsLocally', (pts_rmm_get_settings_value("ArchiveResultsLocally") == 1 ? "TRUE" : "FALSE"));
	$xml_writer->addXmlNode('PhoronixTestSuite/Phoromatic/General/UploadTestLogs', (pts_rmm_get_settings_value("UploadTestLogsToServer") == 1 ? "TRUE" : "FALSE"));
	$xml_writer->addXmlNode('PhoronixTestSuite/Phoromatic/General/UploadSystemLogs', (pts_rmm_get_settings_value("UploadSystemLogsToServer") == 1 ? "TRUE" : "FALSE"));
	$xml_writer->addXmlNode('PhoronixTestSuite/Phoromatic/General/RunInstallCommand', (pts_rmm_get_settings_value("RunInstallCommand") == 1 ? "TRUE" : "FALSE"));
	$xml_writer->addXmlNode('PhoronixTestSuite/Phoromatic/General/ForceInstallTests', (pts_rmm_get_settings_value("ForceInstallTests") == 1 ? "TRUE" : "FALSE"));
	$xml_writer->addXmlNode('PhoronixTestSuite/Phoromatic/General/SetContextPreInstall', $record->SetContextPreInstall);
	$xml_writer->addXmlNode('PhoronixTestSuite/Phoromatic/General/SetContextPreRun', $record->SetContextPreRun);
	$xml_writer->addXmlNode('PhoronixTestSuite/Phoromatic/General/Trigger', $trigger_string);
	$xml_writer->addXmlNode('PhoronixTestSuite/Phoromatic/General/ID', $schedule_id);

	$xml_writer->addXmlNode('PhoronixTestSuite/SuiteInformation/Title', $record->Title);
	$xml_writer->addXmlNode('PhoronixTestSuite/SuiteInformation/Version', "1.0.0");
	$xml_writer->addXmlNode('PhoronixTestSuite/SuiteInformation/Maintainer', $record->LastModifiedBy);
	$xml_writer->addXmlNode('PhoronixTestSuite/SuiteInformation/TestType', "System");
	$xml_writer->addXmlNode('PhoronixTestSuite/SuiteInformation/Description', $record->Description);

	$result = mysql_query("SELECT Test, TestArguments, TestDescription FROM schedules_tests WHERE AccountID = '" . AID . "' AND ScheduleID = '" . $schedule_id . "'");

	while($record = mysql_fetch_object($result))
	{
		$xml_writer->addXmlNode('PhoronixTestSuite/Execute/Test', $record->Test);

		if($record->TestArguments != null && $record->TestDescription != null)
		{
			$xml_writer->addXmlNode('PhoronixTestSuite/Execute/Arguments', $record->TestArguments);
			$xml_writer->addXmlNode('PhoronixTestSuite/Execute/Description', $record->TestDescription);
		}
	}
}
else
{
	$todo = 'idle';

	if(pts_rmm_get_settings_value('ExitDailyWhenDone') == 1)
	{
		// like the above command but with "AND RunAt <= CURRENT_TIME()" removed
		$result = mysql_query("SELECT RunAt FROM schedules WHERE AccountID = '" . AID . "' AND Status = 'ACTIVE' AND SUBSTRING(ActiveOn, DAYOFWEEK(NOW()), 1) = '1' AND (SELECT COUNT(SystemID) FROM schedules_systems WHERE AccountID = '" . AID . "' AND ScheduleID = schedules.ScheduleID AND (SystemID = '" . SID . "' OR SystemID = '0') LIMIT 1) NOT LIKE '0' AND (SELECT COUNT(Test) FROM schedules_tests WHERE AccountID = '" . AID . "' AND ScheduleID = schedules.ScheduleID LIMIT 1) NOT LIKE '0' AND " . SID . " NOT IN (SELECT SystemID FROM test_runs WHERE DATE(UploadTime) = CURRENT_DATE() AND TIME(UploadTime) > schedules.RunAt AND ScheduleID = schedules.ScheduleID) LIMIT 1");

		if(mysql_num_rows($result) == 0)
		{
			$todo = 'exit';
		}
	}

	$xml_writer->addXmlNode('PhoronixTestSuite/Phoromatic/General/Response', $todo);

}

echo $xml_writer->getXML();

?>