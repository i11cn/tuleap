<?php
/**
 * Copyright (c) Enalean, 2017. All Rights Reserved.
 * Copyright 1999-2000 (c) The SourceForge Crew
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

require_once __DIR__ . '/../include/pre.php';
require_once __DIR__ . '/../include/trove.php';

if ($GLOBALS['sys_use_trove'] == 0) {
    exit_permission_denied();
}

$current_user = $request->getCurrentUser();

$trove_cat_dao = new TroveCatDao();

$request = HTTPRequest::instance();
if ($request->exist('form_cat')) {
    $form_cat = intval($request->get('form_cat'));
} else {
    $res_rootcat = db_query("SELECT trove_cat_id FROM trove_cat WHERE parent=0 ORDER BY fullname LIMIT 1");
    $form_cat = db_fetch_array($res_rootcat)['trove_cat_id'];
}

$special_cat = $request->getValidated('special_cat');

// get info about current folder
$res_trove_cat = db_query('SELECT * FROM trove_cat WHERE trove_cat_id=' . $form_cat);
if (db_numrows($res_trove_cat) < 1) {
    $category = $trove_cat_dao->getParentCategoriesUnderRoot();
    if ($category->count() === 0) {
        exit_error(
            $Language->getText('softwaremap_trove_list', 'invalid_cat'),
            $Language->getText('softwaremap_trove_list', 'cat_not_exist')
        );
    }
    $res_trove_cat = $category->getRow();
}
$row_trove_cat = db_fetch_array($res_trove_cat);

$current_category_name = $row_trove_cat['fullpath'];

$folders = explode(" :: ",$row_trove_cat['fullpath']);
$folders_ids = explode(" :: ",$row_trove_cat['fullpath_ids']);
$folders_len = count($folders);

$parent_id = null;
if ($folders_len > 1) {
    $parent_id = $folders_ids[$folders_len - 2];
}

$sub_categories = array();

$sql = "SELECT t.trove_cat_id AS trove_cat_id, t.fullname AS fullname, SUM(IFNULL(t3.nb, 0)) AS subprojects 
FROM trove_cat AS t, trove_cat AS t2 LEFT JOIN (SELECT t.trove_cat_id AS trove_cat_id, count(t.group_id) AS nb
FROM trove_group_link AS t INNER JOIN groups AS g USING(group_id)
WHERE " .trove_get_visibility_for_user('g.access', $current_user). "
  AND g.status = 'A'
  AND g.type = 1
GROUP BY trove_cat_id) AS t3 USING(trove_cat_id)
WHERE t.parent = $form_cat
  AND (
      t2.fullpath_ids LIKE CONCAT(t.trove_cat_id, ' ::%')
   OR t2.fullpath_ids LIKE CONCAT('%:: ', t.trove_cat_id, ' ::%')
   OR t2.fullpath_ids LIKE t.trove_cat_id
   OR t2.fullpath_ids LIKE CONCAT('%:: ', t.trove_cat_id)
      )
GROUP BY t.trove_cat_id
ORDER BY fullname";
$res_sub = db_query($sql);
while ($row_sub = db_fetch_array($res_sub)) {
    $sub_categories[] = new Tuleap\Trove\TroveCatCategoryPresenter(
        $row_sub['trove_cat_id'],
        $row_sub['fullname'],
        (int) $row_sub['subprojects'],
        $row_sub['trove_cat_id'] == $form_cat
    );
}

// MV: Add a None case
if($folders_len == 1) {
    $sql = "SELECT count(DISTINCT g.group_id) AS count
FROM groups AS g
LEFT JOIN trove_group_link AS t
USING ( group_id )
WHERE " .trove_get_visibility_for_user('access', $current_user). "
AND STATUS = 'A'
AND TYPE =1
AND trove_cat_root = ". $form_cat;
    $res_nb = db_query($sql);
    $row_nb = db_fetch_array($res_nb);

    $res_total = db_query("SELECT count(*) as count FROM groups WHERE " .trove_get_visibility_for_user('access', $current_user). " AND status='A' and type=1");
    $row_total = db_fetch_array($res_total);
    $nb_not_cat=$row_total['count']-$row_nb['count'];

    $sub_categories[] = new Tuleap\Trove\TroveCatCategoryNonePresenter(
        $form_cat,
        $nb_not_cat,
        $special_cat == 'none'
    );
}

// here we print list of root level categories, and use open folder for current
$root_categories = array();
$res_rootcat = db_query('SELECT trove_cat_id,fullname FROM trove_cat WHERE '
    .'parent=0 ORDER BY fullname');
while ($row_rootcat = db_fetch_array($res_rootcat)) {
    $root_categories[] = array(
        'id'       => $row_rootcat['trove_cat_id'],
        'name'     => $row_rootcat['fullname'],
        'selected' => $row_rootcat['trove_cat_id'] == $folders_ids[0],
    );
}

if ($special_cat === 'none') {
    $qry_root_trov = 'SELECT group_id'
        .' FROM trove_group_link'
        .' WHERE trove_cat_root='.$form_cat
        .' GROUP BY group_id';
    $res_root_trov = db_query($qry_root_trov);

    $prj_list_categorized = array();
    while($row_root_trov = db_fetch_array($res_root_trov)) {
        $prj_list_categorized[] = $row_root_trov['group_id'];
    }

    $sql_list_categorized='';
    if(count($prj_list_categorized) > 0) {
        $sql_list_categorized=' AND groups.group_id NOT IN ('.implode(',', $prj_list_categorized).') ';
    }
    $query_projlist = "SELECT SQL_CALC_FOUND_ROWS groups.group_id, "
        . "groups.group_name, "
        . "groups.unix_group_name, "
        . "groups.status, "
        . "groups.register_time, "
        . "groups.short_description, "
        . "project_metric.percentile, "
        . "project_metric.ranking "
        . "FROM groups "
        . "LEFT JOIN project_metric USING (group_id) "
        . "WHERE "
        . "(" .trove_get_visibility_for_user('groups.access', $current_user). ") AND "
    . "(groups.type=1) AND "
        . "(groups.status='A') "
        . $sql_list_categorized
        . "GROUP BY groups.group_id ORDER BY groups.group_name ";
}
else {
// now do limiting query

    $query_projlist = "SELECT SQL_CALC_FOUND_ROWS groups.group_id, "
    . "groups.group_name, "
    . "groups.unix_group_name, "
    . "groups.status, "
    . "groups.register_time, "
    . "groups.short_description, "
    . "project_metric.percentile, "
    . "project_metric.ranking "
    . "FROM groups "
    . "LEFT JOIN project_metric USING (group_id) "
    . ", trove_group_link "
    . "WHERE trove_group_link.group_id=groups.group_id AND "
    . "(" .trove_get_visibility_for_user('groups.access', $current_user). ") AND "
        . "(groups.type=1) AND "
    . "(groups.status='A') AND "
    . "trove_group_link.trove_cat_id=$form_cat "
    . "GROUP BY groups.group_id ORDER BY groups.group_name ";
}

$limit  = $TROVE_BROWSELIMIT;
$offset = (int) $request->getValidated('offset', 'uint', 0);
$query_projlist .= " LIMIT $limit OFFSET $offset ";

$res_grp = db_query($query_projlist);

$res_count = db_query('SELECT FOUND_ROWS() as nb');
$row_count = db_fetch_array($res_count);
$total_nb_projects = $row_count['nb'];

$collection_retriever = new \Tuleap\Trove\TroveCatCollectionRetriever($trove_cat_dao);

$projects = array();
while ($row = db_fetch_array($res_grp)) {
    $projects[]= array(
        'longname'    => util_unconvert_htmlspecialchars($row['group_name']),
        'shortname'   => strtolower($row['unix_group_name']),
        'description' => $row['short_description'],
        'trovecats'   => $collection_retriever->getCollection($row['group_id']),
        'date'        => format_date($GLOBALS['Language']->getText('system', 'datefmt'), $row['register_time']),
    );
}

$pagination_params = array(
    'form_cat' => $form_cat
);
if ($special_cat) {
    $pagination_params['special_cat'] = $special_cat;
}

$renderer = TemplateRendererFactory::build()->getRenderer(ForgeConfig::get('codendi_dir') . '/src/templates/softwaremap');

$GLOBALS['HTML']->header(array('title' => $Language->getText('softwaremap_trove_list','map'), 'main_classes' => array('tlp-framed')));

$renderer->renderToPage(
    'software_map',
    new \Tuleap\Trove\SoftwareMapPresenter(
        $current_category_name,
        $parent_id,
        $sub_categories,
        $root_categories,
        $projects,
        new \Tuleap\Layout\PaginationPresenter(
            $TROVE_BROWSELIMIT,
            $offset,
            count($projects),
            $total_nb_projects,
            '/softwaremap/trove_list.php',
            $pagination_params
        )
    )
);

$HTML->footer(array());
