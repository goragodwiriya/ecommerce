<?php
	// modules/member/province.php
	header("content-type: text/xml; charset=UTF-8");
	// inint
	include '../../bin/inint.php';
	// referer
	if (gcms::isReferer()) {
		// ค่าที่รับมา หรือค่าจังหวัด ตำบล อำเภอที่เลือก
		$province = $_REQUEST['province'];
		$district = $_REQUEST['district'];
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		echo "<items>";
		// จังหวัด
		echo "<province>";
		foreach ($db->customQuery("SELECT id, name FROM ".DB_PROVINCE) AS $item) {
			echo "<a$item[id]>$item[name]</a$item[id]>";
		}
		echo "</province>";
		// อำเภอ
		echo "<district>";
		foreach ($db->customQuery("SELECT id, name FROM ".DB_DISTRICT." WHERE provinceID='$province'") AS $item) {
			echo "<a$item[id]>$item[name]</a$item[id]>";
		}
		echo "</district>";
		// ตำบล
		echo "<tambon>";
		foreach ($db->customQuery("SELECT id, name FROM ".DB_TAMBON." WHERE districtID='$district'") AS $item) {
			echo "<a$item[id]>$item[name]</a$item[id]>";
		}
		echo "</tambon>";
		// zipcode
		echo "<zipcode>";
		foreach ($db->customQuery("SELECT zipcode FROM ".DB_ZIPCODE." WHERE province='$province' GROUP BY zipcode") AS $item) {
			echo "<a$item[zipcode]>$item[zipcode]</a$item[zipcode]>";
		}
		echo "</zipcode>";
		echo "</items>";
	}
