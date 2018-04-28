<?php
	// widgets/rss/admin_setup.php
	if (MAIN_INIT == 'admin' && $isAdmin) {
		// รายการที่แก้ไข
		$id = (int)$_GET['id'];
		// title
		$title = $lng['LNG_RSS_TAB'];
		$a = array();
		$a[] = '<span class=icon-widgets>{LNG_WIDGETS}</span>';
		$a[] = '{LNG_RSS_TAB}';
		// แสดงผล
		$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
		$content[] = '<section>';
		$content[] = '<header><h1 class=icon-rss>'.$title.'</h1></header>';
		$content[] = '<div class=setup_frm>';
		// รายการ urls
		$content[] = '<form id=setup_frm class=paper method=post action=index.php>';
		$content[] = '<fieldset>';
		$content[] = '<legend><span>{LNG_RSS_TAB}</span></legend>';
		$content[] = '<div class=subtitle>{LNG_RSS_TAB_SUBTITLE}</div>';
		// url
		$content[] = '<div class=item>';
		$content[] = '<label for=rss_url>{LNG_URL}</label>';
		$content[] = '<span class="g-input icon-world"><input type=text id=rss_url name=rss_url title="{LNG_RSS_URL_COMMENT}"></span>';
		$content[] = '<div class=comment id=result_rss_url>{LNG_RSS_URL_COMMENT}</div>';
		$content[] = '</div>';
		// topic
		$content[] = '<div class=item>';
		$content[] = '<label for=rss_topic>{LNG_TOPIC}</label>';
		$content[] = '<span class="g-input icon-edit"><input type=text id=rss_topic name=rss_topic title="{LNG_RSS_TOPIC_COMMENT}"></span>';
		$content[] = '<div class=comment id=result_rss_topic>{LNG_RSS_TOPIC_COMMENT}</div>';
		$content[] = '</div>';
		// index
		$content[] = '<div class=item>';
		$content[] = '<label for=rss_index>{LNG_ID}</label>';
		$content[] = '<div class="table collapse">';
		$content[] = '<div class=td><span class="g-input icon-edit"><input type=text id=rss_index name=rss_index maxlength=1 title="{LNG_RSS_ID_COMMENT}" pattern="[0-9]+"></span></div>';
		$content[] = '<div class=td>&nbsp;<em id=rss_index_result></em></div>';
		$content[] = '</div>';
		$content[] = '<div class=comment id=result_rss_index>{LNG_RSS_ID_COMMENT}</div>';
		$content[] = '</div>';
		// rows,cols
		$content[] = '<div class=item>';
		$content[] = '<label for=rss_cols>{LNG_DISPLAY}</label>';
		$content[] = '<div class="table collapse">';
		$content[] = '<label class=td for=rss_cols>{LNG_COLS}&nbsp;</label>';
		$content[] = '<div class=td><span class="g-input icon-height"><input type=number name=rss_cols id=rss_cols value='.$config['widget_gallery_cols'].' title="{LNG_DISPLAY_ROWS_COLS_COMMENT}"></span></div>';
		$content[] = '<label class=td for=rss_rows>&nbsp;{LNG_ROWS}&nbsp;</label>';
		$content[] = '<div class=td><span class="g-input icon-width"><input type=number name=rss_rows id=rss_rows value='.$config['widget_gallery_rows'].' title="{LNG_DISPLAY_ROWS_COLS_COMMENT}"></span></div>';
		$content[] = '</div>';
		$content[] = '<div class=comment>{LNG_COLS_COUNT_COMMENT}</div>';
		$content[] = '</div>';
		$content[] = '</fieldset>';
		$content[] = '<fieldset class=submit>';
		$content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
		$content[] = '&nbsp;<input type=button class="button large cancle" value="{LNG_CANCLE}" id=rss_reset>';
		$content[] = '<input type=hidden name=rss_id id=rss_id value=0>';
		$content[] = '</fieldset>';
		$content[] = '</form>';
		// ตารางรายการ rss
		$content[] = '<table id=member class="tbl_list fullwidth">';
		$patt2 = array('/{SEARCH}/', '/{COUNT}/', '/{PAGE}/', '/{TOTALPAGE}/', '/{START}/', '/{END}/');
		$replace2 = array('', sizeof($config['rss_tabs']), 1, 1, 1, sizeof($config['rss_tabs']));
		$content[] = '<caption>'.preg_replace($patt2, $replace2, $lng['ALL_ITEMS']).'</caption>';
		$content[] = '<thead>';
		$content[] = '<tr>';
		$content[] = '<th scope=col id=c1 class=mobile>{LNG_URL}</th>';
		$content[] = '<th scope=col id=c2 class=check-column><a class="checkall icon-uncheck"></a></th>';
		$content[] = '<th scope=col id=c3>{LNG_TOPIC}</th>';
		$content[] = '<th scope=col id=c4 class=tablet></th>';
		$content[] = '<th scope=col id=c5 class="center tablet">{LNG_ID}</th>';
		$content[] = '<th scope=col id=c6 class="center tablet">{LNG_ROWS} * {LNG_COLS}</th>';
		$content[] = '<th scope=col id=c7></th>';
		$content[] = '</tr>';
		$content[] = '</thead>';
		$content[] = '<tbody>';
		if (is_array($config['rss_tabs'])) {
			foreach ($config['rss_tabs'] AS $id => $item) {
				$url = htmlspecialchars($item[0]);
				$tr = '<tr id="L_'.$id.'" class=sort>';
				$tr .= '<th headers=c1 id=r'.$id.' scope=row class=mobile><a href="'.$url.'" target=_blank>'.$url.'</a></th>';
				$tr .= '<td headers="r'.$id.' c2" class=check-column><a id=check_'.$id.' class=icon-uncheck></a></td>';
				$tr .= '<td headers="r'.$id.' c3">'.$item[1].'</td>';
				$tr .= '<td headers="r'.$id.' c4" class="move center tablet"><a id=move_'.$id.' title="{LNG_DRAG_MOVE}"></a></td>';
				$tr .= '<td headers="r'.$id.' c5" class="center tablet">'.$item[2].'</td>';
				$tr .= '<td headers="r'.$id.' c6" class="center tablet">'.$item[3].' * '.$item[4].'</td>';
				$tr .= '<td headers="r'.$id.' c7" class=menu><a id=edit_'.$id.' href="index.php?module=rss-setup&amp;id='.$id.'" class=icon-edit></a></td>';
				$tr .= '</tr>';
				$content[] = $tr;
			}
		}
		$content[] = '</tbody>';
		$content[] = '<tfoot>';
		$content[] = '<tr>';
		$content[] = '<td headers=c1 class=mobile></td>';
		$content[] = '<td headers=c2 class=check-column><a class="checkall icon-uncheck"></a></td>';
		$content[] = '<td headers=c3 colspan=6></td>';
		$content[] = '</tr>';
		$content[] = '</tfoot>';
		$content[] = '</table>';
		$content[] = '<div class=table_nav>';
		// sel action
		$content[] = '<select id=sel_action><option value=delete>{LNG_DELETE}</option></select>';
		$content[] = '<label accesskey=e for=sel_action class="button go" id=btn_action><span>{LNG_SELECT_ACTION}</span></label>';
		$content[] = '</div>';
		$content[] = '</div>';
		$content[] = '</section>';
		$content[] = '<script>';
		$content[] = '$G(window).Ready(function(){';
		$content[] = 'new GForm("setup_frm", "'.WEB_URL.'/widgets/rss/admin_setup_save.php").onsubmit(doRSSSubmit);';
		$content[] = "inintCheck('member');";
		$content[] = "inintTR('member', /L_[0-9]+/);";
		$content[] = 'callAction("btn_action", function(){return $E("sel_action").value}, "member", "'.WEB_URL.'/widgets/rss/admin_action.php");';
		$content[] = 'inintList("member", "a", /edit_[0-9]+/, "'.WEB_URL.'/widgets/rss/admin_action.php", doRSSSubmit);';
		$content[] = 'callClick("rss_reset", rssReset);';
		$content[] = '$G("rss_index").addEvent("keyup", rssIndexChanged)';
		$content[] = 'rssIndexChanged.call($G("rss_index"));';
		$content[] = 'doInintRSSSetup("member");';
		$content[] = '});';
		$content[] = '</script>';
		// หน้านี้
		$url_query['module'] = 'rss-setup';
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
